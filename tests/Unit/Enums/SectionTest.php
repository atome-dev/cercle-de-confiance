<?php

use App\Enums\Section;

test('every section has a non-empty label', function () {
    foreach (Section::cases() as $section) {
        expect($section->label())->not->toBeEmpty();
    }
});
