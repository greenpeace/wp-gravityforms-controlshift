<?php

declare(strict_types=1);

namespace P4\ControlShift\API;

class PublicEndpoint {
	public static function petition( string $petition ): string {
		return '/petitions/' . $petition . '.json';
	}

	public static function featured_petitions(): string {
		return '/petitions/featured.json';
	}

	public static function categories(): string {
		return '/categories.json';
	}

	public static function petitions_in_category( string $category ): string {
		return '/categories/' . $category . '.json';
	}

	public static function petitions_in_partnership( string $partnership ): string {
		return '/partnership/' . $partnership . '/petitions.json';
	}

	public static function petitions_in_effort( string $effort ): string {
		return '/efforts/' . $effort . '.json';
	}
}
