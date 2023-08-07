<?php

namespace App\Bot\Contracts;

interface ObjectContract {    
    /**
     * build
     *
     * @return void
     */
    public function build(): void;

    /**
     * toJson
     *
     * @return string
     */
    public function toJson(): string;
        
    /**
     * toArray
     *
     * @return array
     */
    public function toArray(): array;
}