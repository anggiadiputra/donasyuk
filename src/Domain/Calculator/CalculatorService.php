<?php
namespace DonasiYuk\Domain\Calculator;

class CalculatorService implements CalculatorServiceInterface {
    // Nisab zakat maal/profesi: 85g gold
    private const NISAB_GOLD_GRAMS = 85.0;
    private const ZAKAT_RATE = 0.025; // 2.5%

    public function calculateZakatMaal(float $totalAssets, float $goldPricePerGram = 1300000.0): array {
        $nisabValue = self::NISAB_GOLD_GRAMS * $goldPricePerGram;
        $isWajib = $totalAssets >= $nisabValue;
        $zakatAmount = $isWajib ? ($totalAssets * self::ZAKAT_RATE) : 0.0;

        return [
            'total_assets' => $totalAssets,
            'gold_price' => $goldPricePerGram,
            'nisab_threshold' => $nisabValue,
            'is_wajib' => $isWajib,
            'zakat_amount' => round($zakatAmount, 2),
        ];
    }

    public function calculateZakatProfesi(float $monthlyIncome, float $monthlyExpenses = 0.0, float $goldPricePerGram = 1300000.0): array {
        $netIncome = max(0.0, $monthlyIncome - $monthlyExpenses);
        $monthlyNisab = (self::NISAB_GOLD_GRAMS * $goldPricePerGram) / 12.0;
        $isWajib = $netIncome >= $monthlyNisab;
        $zakatAmount = $isWajib ? ($netIncome * self::ZAKAT_RATE) : 0.0;

        return [
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'net_income' => $netIncome,
            'monthly_nisab' => round($monthlyNisab, 2),
            'is_wajib' => $isWajib,
            'zakat_amount' => round($zakatAmount, 2),
        ];
    }

    public function calculateQurbanAnimal(int $personCount, string $animalType = 'kambing'): array {
        $type = strtolower($animalType);
        $shareRatio = ($type === 'sapi' || $type === 'unta') ? 7 : 1;
        $animalsNeeded = (int) ceil($personCount / $shareRatio);

        return [
            'person_count' => $personCount,
            'animal_type' => $type,
            'share_capacity' => $shareRatio,
            'animals_needed' => $animalsNeeded,
        ];
    }
}
