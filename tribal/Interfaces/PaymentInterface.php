<?php 
namespace Tribal\Interfaces;


interface PaymentInterface
{
    public function transaction($transaction_data);
}