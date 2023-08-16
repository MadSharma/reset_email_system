<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminProfileTabs extends Component
{
    public $tab = 'personal_details'; // Default tab
    protected $queryString = ['tab'];
    public $name, $email, $username, $admin_id;

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

    public function updateAdminPersonDetails(){
        $this->validate([
            'name' => 'required|min:5',
            'email' => 'required|email|unique:admins,email,'.$this->admin_id,
            'username' => 'required|min:3|unique:admins,username,'.$this->admin_id,
        ]);

        Admin::find($this->admin_id)->update([
            'name' =>  $this->name,
            'email' =>  $this->email,
            'username' => $this->username
        ]);

        $this->emit('updateAdminSellerHeaderInfo');
        $this->dispatchBrowserEvent('updateAdminInfo',[
            'adminName' =>$this->name,
            'adminEmail' =>$this->email

        ]);
        $this->showToastr('success', 'Your personal details have been successfully updated.');
    }

    public function showToastr($type, $message){
        return $this->dispatchBrowserEvent('showToastr',[
            'type' => $type,
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.admin-profile-tabs');
    }
}
