<?php

namespace App\Livewire;

use App\Models\Olt;
use App\Services\OltManagementService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class SshSettings extends Component
{
    public $olts = [];
    public $selectedOlt = null;
    public $sshUsername = '';
    public $sshPassword = '';
    public $sshPort = 22;
    public $sshEnabled = false;
    public $isTesting = false;
    public $testResult = '';
    public $isSaving = false;
    public $saveResult = '';

    protected $listeners = ['oltSelected', 'testConnection', 'saveSettings'];

    public function mount()
    {
        $this->loadOlts();
    }

    public function loadOlts()
    {
        $this->olts = Olt::orderBy('name')->get();
    }

    public function oltSelected($oltId)
    {
        $this->selectedOlt = Olt::find($oltId);
        if ($this->selectedOlt) {
            $this->sshUsername = $this->selectedOlt->ssh_username ?: '';
            $this->sshPassword = $this->selectedOlt->ssh_password ?: '';
            $this->sshPort = $this->selectedOlt->ssh_port ?: 22;
            $this->sshEnabled = $this->selectedOlt->ssh_enabled ?: false;
        }
        $this->testResult = '';
        $this->saveResult = '';
    }

    public function testConnection()
    {
        if (!$this->selectedOlt) {
            $this->testResult = 'Please select an OLT first.';
            return;
        }

        $this->isTesting = true;
        $this->testResult = '';

        try {
            // Create a temporary OLT object with custom port for testing
            $testOlt = clone $this->selectedOlt;
            $testOlt->ssh_username = $this->sshUsername;
            $testOlt->ssh_password = $this->sshPassword;
            $testOlt->ssh_port = $this->sshPort;

            $managementService = new OltManagementService();
            $connection = $managementService->connectToOlt(
                $testOlt,
                $this->sshUsername,
                $this->sshPassword
            );

            if ($connection) {
                $this->testResult = '✅ SSH connection successful!';
                Log::info("SSH test successful for OLT {$this->selectedOlt->name} on port {$this->sshPort}");
            } else {
                $this->testResult = '❌ SSH connection failed.';
            }
        } catch (\Exception $e) {
            $this->testResult = '❌ SSH connection failed: ' . $e->getMessage();
            Log::error("SSH test failed for OLT {$this->selectedOlt->name} on port {$this->sshPort}: " . $e->getMessage());
        }

        $this->isTesting = false;
    }

    public function saveSettings()
    {
        if (!$this->selectedOlt) {
            $this->saveResult = 'Please select an OLT first.';
            return;
        }

        $this->isSaving = true;
        $this->saveResult = '';

        try {
            $this->selectedOlt->update([
                'ssh_username' => $this->sshUsername,
                'ssh_password' => $this->sshPassword,
                'ssh_port' => $this->sshPort,
                'ssh_enabled' => $this->sshEnabled,
            ]);

            $this->saveResult = '✅ SSH settings saved successfully!';
            Log::info("SSH settings updated for OLT {$this->selectedOlt->name} with port {$this->sshPort}");
        } catch (\Exception $e) {
            $this->saveResult = '❌ Failed to save settings: ' . $e->getMessage();
            Log::error("Failed to save SSH settings for OLT {$this->selectedOlt->name}: " . $e->getMessage());
        }

        $this->isSaving = false;
    }

    public function resetForm()
    {
        $this->sshUsername = '';
        $this->sshPassword = '';
        $this->sshPort = 22;
        $this->sshEnabled = false;
        $this->testResult = '';
        $this->saveResult = '';
    }

    public function render()
    {
        return view('livewire.ssh-settings');
    }
}