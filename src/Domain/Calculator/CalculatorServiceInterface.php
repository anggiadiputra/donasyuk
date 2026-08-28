<?php
namespace DonasiYuk\Domain\Calculator;

interface CalculatorServiceInterface {
    public function calculateZakatMaal(float $totalAssets, float $goldPricePerGram = 1300000.0): array;
    public function calculateZakatProfesi(float $monthlyIncome, float $monthlyExpenses = 0.0, float $goldPricePerGram = 1300000.0): array;
    public function calculateQurbanAnimal(int $personCount, string $animalType = 'kambing'): array;
}
