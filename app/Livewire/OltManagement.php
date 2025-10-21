<?php

namespace App\Livewire;

use App\Models\Olt;
use App\Models\Onu;
use App\Services\OltManagementService;
use Livewire\Component;

class OltManagement extends Component
{
    public $selectedOlt;
    public $selectedOnu;
    public $username = '';
    public $password = '';
    public $command = '';
    public $output = '';
    public $showModal = false;

    protected $listeners = ['showManagementModal' => 'showModal'];

    public function showModal($oltId, $onuId = null)
    {
        $this->selectedOlt = $oltId;
        $this->selectedOnu = $onuId;
        $this->showModal = true;
        $this->reset(['username', 'password', 'command', 'output']);
    }

    public function rebootOnu()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $olt = Olt::find($this->selectedOlt);
        $onu = Onu::find($this->selectedOnu);

        if (!$olt || !$onu) {
            $this->addError('error', 'OLT or ONU not found');
            return;
        }

        $service = new OltManagementService();
        $result = $service->rebootOnu($olt, $onu, $this->username, $this->password);

        if ($result['success']) {
            $this->output = $result['message'] . "\n\n" . $result['output'];
            session()->flash('success', 'ONU reboot command executed successfully');
        } else {
            $this->addError('error', $result['message']);
        }
    }

    public function executeCommand()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
            'command' => 'required',
        ]);

        $olt = Olt::find($this->selectedOlt);

        if (!$olt) {
            $this->addError('error', 'OLT not found');
            return;
        }

        $service = new OltManagementService();
        $result = $service->executeCustomCommand($olt, $this->command, $this->username, $this->password);

        if ($result['success']) {
            $this->output = $result['output'];
        } else {
            $this->addError('error', $result['error']);
        }
    }

    public function getOnuStatus()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $olt = Olt::find($this->selectedOlt);
        $onu = Onu::find($this->selectedOnu);

        if (!$olt || !$onu) {
            $this->addError('error', 'OLT or ONU not found');
            return;
        }

        $service = new OltManagementService();
        $result = $service->getOnuStatus($olt, $onu, $this->username, $this->password);

        if ($result['success']) {
            $this->output = $result['output'];
        } else {
            $this->addError('error', $result['error']);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['selectedOlt', 'selectedOnu', 'username', 'password', 'command', 'output']);
    }

    public function render()
    {
        $olts = Olt::where('is_active', true)->get();
        $onus = $this->selectedOlt ? Onu::where('olt_id', $this->selectedOlt)->get() : collect();
        
        return view('livewire.olt-management', [
            'olts' => $olts,
            'onus' => $onus
        ]);
    }
}
