<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Enum;

enum ListingPeriod: string
{
    case TODAY = 'today';
    case THIS_WEEK = 'this-week';
    case THIS_MONTH = 'this-month';
    case THIS_YEAR = 'this-year';
    case LAST_7_DAYS = 'last-7-days';
    case LAST_30_DAYS = 'last-30-days';
    case LAST_12_MONTHS = 'last-12-months';
    case LAST_YEAR = 'last-year';

    public function label(): string
    {
        return match ($this) {
            self::TODAY => __("Aujourd'hui", 'horizon-blocks'),
            self::THIS_WEEK => __('Cette semaine', 'horizon-blocks'),
            self::THIS_MONTH => __('Ce mois-ci', 'horizon-blocks'),
            self::THIS_YEAR => __('Cette année', 'horizon-blocks'),
            self::LAST_7_DAYS => __('7 derniers jours', 'horizon-blocks'),
            self::LAST_30_DAYS => __('30 derniers jours', 'horizon-blocks'),
            self::LAST_12_MONTHS => __('12 derniers mois', 'horizon-blocks'),
            self::LAST_YEAR => __("L'an dernier", 'horizon-blocks'),
        };
    }

    /**
     * @return array{0: ?\DateTimeImmutable, 1: ?\DateTimeImmutable} [after, before]
     */
    public function resolve(\DateTimeImmutable $now): array
    {
        $year = (int) $now->format('Y');

        return match ($this) {
            self::TODAY => [$now->setTime(0, 0, 0), $now],
            self::THIS_WEEK => [$now->modify('monday this week')->setTime(0, 0, 0), $now],
            self::THIS_MONTH => [$now->modify('first day of this month')->setTime(0, 0, 0), $now],
            self::THIS_YEAR => [$now->setDate($year, 1, 1)->setTime(0, 0, 0), $now],
            self::LAST_7_DAYS => [$now->modify('-7 days'), $now],
            self::LAST_30_DAYS => [$now->modify('-30 days'), $now],
            self::LAST_12_MONTHS => [$now->modify('-12 months'), $now],
            self::LAST_YEAR => [
                $now->setDate($year - 1, 1, 1)->setTime(0, 0, 0),
                $now->setDate($year - 1, 12, 31)->setTime(23, 59, 59),
            ],
        };
    }

    /**
     * @return string[]
     */
    public static function slugs(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    /**
     * @return array<string, string> slug => label
     */
    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $case) {
            $choices[$case->value] = $case->label();
        }

        return $choices;
    }
}
