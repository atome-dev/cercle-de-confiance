<?php

use App\Enums\SchoolClass;
use App\Enums\Section;

test('every school class has a non-empty label', function () {
    foreach (SchoolClass::cases() as $classe) {
        expect($classe->label())->not->toBeEmpty();
    }
});

test('forSection returns only classes whose section() matches', function () {
    foreach (Section::cases() as $section) {
        $classes = SchoolClass::forSection($section);

        expect($classes)->not->toBeEmpty();

        foreach ($classes as $classe) {
            expect($classe->section())->toBe($section);
        }
    }
});

test('forSection partitions every school class exactly once', function () {
    $partitioned = collect(Section::cases())
        ->flatMap(fn (Section $section) => SchoolClass::forSection($section));

    expect($partitioned->all())->toEqualCanonicalizing(SchoolClass::cases());
});
