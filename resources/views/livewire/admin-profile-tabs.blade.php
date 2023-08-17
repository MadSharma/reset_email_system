<div>
    <div class="profile-tab height-100-p">
        <div class="tab height-100-p">
          <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
              <a  wire:click.prevent="selectTab('personal_details')"  class="nav-link {{ $tab == 'personal_details' ? 'active' : "" }}" data-toggle="tab" href="#personal_details" role="tab">Personal Details</a>
            </li>
            <li class="nav-item">
              <a wire:click.prevent="selectTab('update_password')" class="nav-link  {{ $tab == 'update_password' ? 'active' : "" }}" data-toggle="tab" href="#update_password" role="tab">Update Password</a>
            </li>
          </ul>
          <div class="tab-content">
            <!-- Timeline Tab start -->
            <div class="tab-pane fade {{ $tab == 'personal_details' ? 'active show' : "" }}"  id="personal_details" role="tabpanel">
              <div class="pd-20">
                <form wire:submit.prevent="updateAdminPersonDetails()" >
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="">Name</label>
                        <input type="text" class="form-control" wire:model='name' placeholder="Enter Full Name">
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="">Email</label>
                        <input type="text" class="form-control" wire:model='email' placeholder="Enter email">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="">Username</label>
                        <input type="text" class="form-control" wire:model='username' placeholder="Enter username">
                        @error('username')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary" >Save Changes</button>
                </form>
              </div>
            </div>
            <!-- Timeline Tab End -->
            <!-- Tasks Tab start -->
            <div class="tab-pane fade {{ $tab == 'update_password' ? 'active show' : "" }}" id="update_password" role="tabpanel">
              <div class="pd-20 profile-task-wrap">
                <form wire:submit.prevent ='updatePassword()'>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                      <label for="">Current Password</label>
                      <input type="password" placeholder="Enter current password" wire:model.defer= 'current_password' class="form-control">
                        @error('current_password') 
                          <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                    <label for="">New Password</label>
                    <input type="password" placeholder="Enter new password" wire:model.defer= 'new_password' class="form-control" >
                      @error('new_password') 
                        <span class="text-danger">{{ $message }}</span>
                      @enderror
                  </div>
                </div>
                  <div class="col-md-4">
                    <div class="form-group">
                    <label for="">Confirm New Password</label>
                    <input type="password" placeholder="Retype new password" wire:model.defer= 'new_password_confirmation'class="form-control" >
                      @error('new_password_confirmation') 
                        <span class="text-danger">{{ $message }}</span>
                      @enderror
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary" >Update Password</button>
            </form>
          </div>
        </div>
          </div>
        </div>
      </div>
</div>
