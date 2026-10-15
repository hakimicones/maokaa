<?php

if (!function_exists('welcome_is_legacy_content')) {
    function welcome_is_legacy_content(string $text): bool
    {
        foreach ([
            'Noor Guide',
            'VEP',
            'Votre partenaire incontournable du laboratoire en Algérie',
            'Importation et distribution de matériels et consommables de laboratoire',
            'importateur et distributeur de matériels de laboratoire',
        ] as $legacyText) {
            if (stripos($text, $legacyText) !== false) {
                return true;
            }
        }

        return false;
    }
}
