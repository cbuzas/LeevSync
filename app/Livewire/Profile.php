<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithNotifications;
use Flux\Flux;
use Livewire\Component;

class Profile extends Component
{
    use WithNotifications;

    /**
     * @var \App\Models\Profile[]|\Illuminate\Database\Eloquent\Collection|\LaravelIdea\Helper\App\Models\_IH_Profile_C
     */
    public array|\LaravelIdea\Helper\App\Models\_IH_Profile_C|\Illuminate\Database\Eloquent\Collection $profiles;

    public $name;

    public $description;

    /**
     * @var \App\Models\Profile|mixed
     */
    public mixed $selectedProfile;

    public function mount()
    {
        $this->loadProfiles();
    }

    public function loadProfiles()
    {
        $this->profiles = \App\Models\Profile::all();
    }

    public function openModal($modalName, $key = null)
    {

        $this->reset('name', 'description', 'selectedProfile');

        if ($modalName == 'create') {
            Flux::modal('profile-create')->show();
        } elseif ($modalName == 'update') {
            $this->loadProfileDetails($key);
            Flux::modal('profile-update')->show();
        }
    }

    public function loadProfileDetails($key)
    {
        $profile = $this->profiles[$key];
        if ($profile) {
            $this->selectedProfile = $profile;
            $this->name = $profile->name;
            $this->description = $profile->description;
        }
    }

    public function updateProfile()
    {

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($this->selectedProfile) {
            $this->selectedProfile->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            // Reload profiles
            $this->loadProfiles();

            // Close the modal
            Flux::modal('profile-update')->close();

            $this->dispatch('profileUpdated');

            $this->notifySuccess('Profile updated successfully.', 2000);
        }
    }

    public function createProfile()
    {

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        \App\Models\Profile::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        // Reset form fields
        $this->reset('name', 'description');

        // Reload profiles
        $this->loadProfiles();

        // Close the modal
        Flux::modal('profile-create')->close();

        $this->dispatch('profileAdded');

        $this->notifySuccess('Profile created successfully.', 2000);

    }

    public function deleteProfile()
    {
        if ($this->selectedProfile) {
            $this->selectedProfile->delete();

            // Reload profiles
            $this->loadProfiles();

            // Close the modal
            Flux::modal('profile-update')->close();

            $this->dispatch('profileDeleted');

            $this->notifySuccess('Profile deleted successfully.', 2000);
        }
    }

    public function render()
    {
        return view('livewire.profile', [
            'profiles' => $this->profiles,
        ]);
    }
}
