<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Database\Eloquent\Model;
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CutomerInfo extends Component
{
    public $tenant;
    public $customer;
    public $recordName;
    public $recordNumber;
    public $recordDate;
    public $recordPreparedBy;
    public $fullpath;

    public function __construct($tenant, $customer, $recordName, $recordNumber, $recordDate, $recordPreparedBy, $fullpath)
    {
        $this->tenant = $tenant;
        $this->customer = $customer;
        $this->recordName = $recordName;
        $this->recordNumber = $recordNumber;
        $this->recordDate = $recordDate;
        $this->recordPreparedBy = $recordPreparedBy;
        $this->fullpath = $fullpath;
    }

    public function render(): View
    {
        return view('components.customer-info');
    }
}
