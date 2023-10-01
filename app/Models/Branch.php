<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branches';

    public function __construct() {
        $this->attributes['name'] = get_option('default_branch_name', 'Main Branch');
    }

}