<?php

namespace App\Bot\Objects;

use App\Bot\Contracts\ObjectContract;
use Illuminate\Support\Facades\Log;

class InlineKeyboardButton implements ObjectContract {
    public $data;
    public $columnPerRow;
    public $customColumnPerRow;
    
    /**
     * __construct
     *
     * @param  mixed $data
     * @param  int $columnPerRow
     * @param  string | null $customColumnPerRow
     * @return void
     */
    public function __construct($data, int $columnPerRow = 5, string | null $customColumnPerRow = null)
    {
        $this->columnPerRow = $columnPerRow;
        $this->customColumnPerRow = $customColumnPerRow;

        $this->data = $data;
        $this->build();
    }
    
    /**
     * build
     *
     * @return void
     */
    public function build(): void
    {
        $buttons = [];
        parse_str($this->customColumnPerRow, $customColumnPerRow);
        
        $row = 0;
        $column = 0;
        foreach($this->data as $data) {
            $rowColumn = intval( $customColumnPerRow[ $row ] ?? 0 );
            if($column === $this->columnPerRow || ($rowColumn > 0 && $rowColumn === $column)) {
                $column = 0;
                $row++;
            }

            $buttons[$row][$column] = $data;
            $column++;
        }

        $this->data = $buttons;
    }
    
    /**
     * toJson
     *
     * @return string
     */
    public function toJson(): string
    {
        return is_array($this->data) ? json_encode($this->data) : $this->data;
    }
    
    /**
     * toArray
     *
     * @return array
     */
    public function toArray(): array
    {
        return is_array($this->data) ? $this->data : json_decode($this->data, true);
    }
}