<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\helpers\constGuards;
use App\helpers\constDefaults;

class AdminController extends Controller
{
    const GUARD_ADMIN = 'admin';

    public function loginHandler(Request $request)
    {
        $fieldType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if ($fieldType == 'email') {
            $request->validate([
                'login_id' => 'required|email|exists:admins,email',
                'password' => 'required|min:5|max:45'
            ], [
                'login_id.required' => 'Email or Username is required',
                'login_id.email' => 'Invalid Email address',
                'login_id.exists' => 'Email does not exist in the system',
                'password.required' => 'Password is required'
            ]);
        } else {
            $request->validate([
                'login_id' => 'required|exists:admins,username',
                'password' => 'required|min:5|max:45'
            ], [
                'login_id.required' => 'Email or Username is required',
                'login_id.exists' => 'Username does not exist in the system',
                'password.required' => 'Password is required'
            ]);
        }

        Auth::guard(self::GUARD_ADMIN)->logoutOtherDevices($request->password);

        $creds = [
            $fieldType => $request->login_id,
            'password' => $request->password
        ];

        if (Auth::guard(self::GUARD_ADMIN)->attempt($creds)) {
            return redirect()->route('admin.home');
        } else {
            session()->flash('fail', 'Incorrect credentials');
            return redirect()->route('admin.login');
        }
    }

    public function logoutHandler(Request $request)
    {
        Auth::guard(self::GUARD_ADMIN)->logout();
        session()->flash('fail', 'You are logged out!');
        return redirect()->route('admin.login');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.required' => 'The email address is required.',
            'email.email' => 'Invalid email address.',
            'email.exists' => 'Email does not exist in the system.',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        $token = base64_encode(Str::random(64));

        $oldToken = DB::table('password_reset_tokens')
            ->where(['email' => $request->email, 'guard' => self::GUARD_ADMIN])
            ->first();

        if ($oldToken) {
            DB::table('password_reset_tokens')
                ->where(['email' => $request->email, 'guard' => self::GUARD_ADMIN])
                ->update([
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'guard' => self::GUARD_ADMIN,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        }

        $actionLink = route('admin.reset_password', ['token' => $token, 'email' => $request->email]);

        $data = [
            'actionLink' => $actionLink,
            'admin' => $admin
        ];

        $mail_body = view('email-templates.admin-forgot-email-template', $data)->render();

        $mailConfig = [
            'mail_recipient_email' => $admin->email,
            'mail_recipient_name' => $admin->name,
            'mail_subject' => 'Reset password',
            'mail_body' => $mail_body,
            'mail_form_email' => config('mail.from.address'), // Retrieve sender's email from config
            'mail_form_name' => config('mail.from.name'),   // Retrieve sender's name from config
        ];
        
        if ($this->sendEmail($mailConfig)) {
            session()->flash('success', 'We have emailed your password reset link.');
            return redirect()->route('admin.forgot-password');
        } else {
            session()->flash('fail', 'Email sending failed. Please try again later.');
            return redirect()->route('admin.forgot-password');
        }
    }

    public function sendEmail($mailConfig)
    {
        $mail = new PHPMailer(true);
    
        try {
            $mail->SMTPDebug = 2; // Set to 2 for debugging
            $mail->isSMTP();
            $mail->Host =  env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');
    
            // Set sender and recipient
            $mail->setFrom($mailConfig['mail_form_email'], $mailConfig['mail_form_name']);
            $mail->addAddress($mailConfig['mail_recipient_email'], $mailConfig['mail_recipient_name']);
    
            $mail->isHTML(true);
            $mail->Subject = $mailConfig['mail_subject'];
            $mail->Body = $mailConfig['mail_body'];
    
            $mail->send();
    
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer Error: ' . $e->getMessage()); 
            return false;
        }
    }

    public function resetPassword(Request $request, $token, $email)
    {
        
        $check_token = DB::table('password_reset_tokens')
            ->where(['token' => $token, 'guard' => constGuards::ADMIN])
            ->first();
            // dd($check_token);

        if ($check_token) {
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s', $check_token->created_at)->diffInMinutes(Carbon::now());

            if ($diffMins > constDefaults::tokenExpiredMinutes) {
                session()->flash('fail', 'Token expired! Please request another password reset link.');
                return redirect()->route('admin.forgot-password', ['token' => $token]);
            } else {
                return view('back.pages.admin.auth.reset-password')->with(['token' => $token]);
            }
        } else {
            session()->flash('fail', 'Invalid token! Please request another password reset link.');
            return redirect()->route('admin.forgot-password', ['token' => $token]);
        }       
    }

    public function resetPasswordHandler(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:5|max:45|required_with:new_password_confirmation|same:new_password_confirmation',
            'new_password_confirmation' => 'required'
        ]);

        // Get token details
        $token = DB::table('password_reset_tokens')
            ->where(['token' => $request->token, 'guard' => constGuards::ADMIN])
            ->first();

        if (!$token) {
            session()->flash('fail', 'Invalid token! Please request another password reset link.');
            return redirect()->route('admin.forgot-password', ['token' => $request->token]);
        }

        // Get admin details
        $admin = Admin::where('email', $token->email)->first();

        // Update admin password
        Admin::where('email', $admin->email)->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Delete token record
        DB::table('password_reset_tokens')->where([
            'email' => $admin->email,
            'token' => $request->token,
            'guard' => constGuards::ADMIN
        ])->delete();

        // Send email to notify admin
        $data = array(
            'admin' => $admin,
            'new_password' => $request->new_password
        );

        $mail_body = view('email-templates.admin-reset-email-template', $data)->render();

        $mail_config = array(
            'mail_form_email' => env('MAIL_FROM_ADDRESS'),
            'mail_form_name' => env('MAIL_FROM_NAME'), 
            'mail_recipient_email' => $admin->email,
            'mail_recipient_name' => $admin->name,     
            'mail_subject' => 'Password Changed',
            'mail_body' => $mail_body
        );
        

        $this->sendEmail($mail_config);

        session()->flash('success', 'Your password has been changed. Use the new password to log in.');
        return redirect()->route('admin.login');
    }

    public function profileView(Request $request){
        $admin =null;
        if (Auth::guard('admin')->check() ){
            $admin =Admin::findOrFail(auth()->id());
        } 
        return view('back.pages.admin.profile',compact('admin'));
    }



}

// $token = $request->input('token');
        // echo "<pre>";
        // print_r($request);
        // echo "</pre>";
        // dd($token);