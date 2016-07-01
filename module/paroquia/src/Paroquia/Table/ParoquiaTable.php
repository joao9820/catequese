<?php

namespace Paroquia\Table;

use Estrutura\Table\AbstractEstruturaTable;

class ParoquiaTable extends AbstractEstruturaTable
{
    public $table = 'paroquia';
    public $campos = [
        'id_paroquia' => 'id',
        'id_cidade' => 'id',
        'nm_paroquia' => 'nm_paroquia',



    ];
}