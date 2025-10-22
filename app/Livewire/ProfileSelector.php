<?php

namespace App\Livewire;

use Livewire\Component;

class ProfileSelector extends Component
{

   public $currentProfile;

    public $listeners = [
        'profileUpdated' => 'loadProfiles',
        'profileAdded' => 'loadProfiles',
        'profileDeleted' => 'loadProfiles',
    ];

    public function loadProfiles()
    {

     if(!\App\Models\Profile::exists()) {
            $defaultProfile = \App\Models\Profile::create([
                'name' => 'Default',
                'description' => 'This is the default profile created automatically.'
            ]);
            return collect([$defaultProfile]);
        }

        return \App\Models\Profile::all();

    }


    public function selectedProfil(){

        $currentProfile = session('currentProfileId');

        if($currentProfile){
            $this->currentProfile = $currentProfile;
        } else {
            $firstProfile = \App\Models\Profile::first();
            if($firstProfile){
                $this->currentProfile = $firstProfile->id;
                session(['currentProfileId' => $firstProfile->id]);
            } else {
                $this->currentProfile = null;
            }
        }
    }

    public function changeCurrentProfile()
    {
        $profileId = $this->currentProfile;
        session(['currentProfileId' => $profileId]);
        $this->dispatch('profileChanged');

    }

    public function render()
    {

        $this->selectedProfil();

        return view('livewire.profile-selector', [
            'profiles' => $this->loadProfiles(),
        ]);
    }
}
