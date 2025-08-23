<?php

declare(strict_types=1);

namespace number;

use function number_format;
use function round;

final class Number {
	public static function shortNumber($value): string {
		if (!is_numeric($value)) return "0";
	
		// Define os sufixos e os respectivos valores como strings
		$suffixes = [
			"D"  => "1000000000000000000000000000000000",
			"N"  => "1000000000000000000000000000000",
			"OC" => "1000000000000000000000000000",
			"SS" => "1000000000000000000000000",
			"S"  => "1000000000000000000000",
			"QQ" => "1000000000000000000",
			"Q"  => "1000000000000000",
			"T"  => "1000000000000",
			"B"  => "1000000000",
			"M"  => "1000000",
			"K"  => "1000"
		];
	
		// Converte o valor para string
		$value = (string) $value;
	
		// Percorre os sufixos do maior para o menor
		foreach ($suffixes as $suffix => $threshold) {
			if (bccomp($value, $threshold) >= 0) {
				return bcdiv($value, $threshold, 1) . $suffix;
			}
		}
	
		return (string) $value;
	}

	public static function moneyFormat(int|float $number, bool $float = false, string $decimal = ','): string {
		if ($float) {
			return number_format($number, 2, $decimal, '.');
		}
		return number_format($number, 0, ',', '.');
	}

	public static function toRoman(int $number): string {
		return match ($number) {
			0 => '0',
			1 => 'I',
			2 => 'II',
			3 => 'III',
			4 => 'IV',
			5 => 'V',
			6 => 'VI',
			7 => 'VII',
			8 => 'VIII',
			9 => 'IX',
			10 => 'X',
			default => '0'
		};
	}
}
