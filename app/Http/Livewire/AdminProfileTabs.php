<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminProfileTabs extends Component
{
    public $tab = 'personal_details'; // Default tab
    protected $queryString = ['tab'];
    public $name, $email, $username, $admin_id;
    public $current_password, $new_password, $new_password_confirmation; // Corrected property name

    public function selectTab($selectedTab)
    {
        $this->tab = $selectedTab;
    }

    public function mount()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Admin::findOrFail(auth()->id());
            $this->admin_id = $admin->id;
            $this->name = $admin->name;
            $this->email = $admin->email;
            $this->username = $admin->username;
        }
    }

    public function updateAdminPersonDetails()
    {
        $this->validate([
            'name' => 'required|min:5',
            'email' => 'required|email|unique:admins,email,' . $this->admin_id,
            'username' => 'required|min:3|unique:admins,username,' . $this->admin_id,
        ]);

        Admin::find($this->admin_id)->update([
            'name' =>  $this->name,
            'email' =>  $this->email,
            'username' => $this->username
        ]);

        $this->emit('updateAdminSellerHeaderInfo');
        $this->dispatchBrowserEvent('updateAdminInfo', [
            'adminName' => $this->name,
            'adminEmail' => $this->email
        ]);
        $this->showToastr('success', 'Your personal details have been successfully updated.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => [
                'required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Admin::find(auth('admin')->id())->password)) {
                        return $fail(__('The current password is incorrect'));
                    }
                }
            ],
            'new_password' => 'required|min:5|max:45|confirmed'
        ]);
        
        $query =Admin::findOrFail(auth('admin')->id())->update([
            'password' =>Hash::make($this->new_password)
        ]);

        if($query){
            $this->current_password = $this->new_password =  $this->new_password_comfirmation =null;
            $this->showToastr('success', 'Your Password Updated Successfully.');
        }else{
            $this->showToastr('error', 'something went wrong.');
        }
    }

    public function showToastr($type, $message)
    {
        return $this->dispatchBrowserEvent('showToastr', [
            'type' => $type,
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.admin-profile-tabs');
    }
}
