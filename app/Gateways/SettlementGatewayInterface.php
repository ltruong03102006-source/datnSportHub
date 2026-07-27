<?php
namespace App\Gateways;

interface SettlementGatewayInterface
{
    public function processPayout(string $referenceId, float $amount, array $bankInfo): void;
}