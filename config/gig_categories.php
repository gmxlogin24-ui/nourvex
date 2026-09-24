<?php

// Single source of truth for gig categories and their category-specific
// extra fields. GigController, OnboardingController, and the create/edit
// forms all read from this file - add a category here and it shows up
// everywhere automatically instead of needing to be added in several places.

return [
    'Tutoring' => ['fields' => [
        ['key' => 'subject', 'label' => 'Subject', 'type' => 'text'],
        ['key' => 'level', 'label' => 'Level', 'type' => 'text'],
        ['key' => 'format', 'label' => 'Format', 'type' => 'select', 'options' => ['Online', 'In-person']],
    ]],
    'Typing & Printing' => ['fields' => [
        ['key' => 'pages', 'label' => 'Number of pages', 'type' => 'text'],
        ['key' => 'color', 'label' => 'Color or black and white', 'type' => 'select', 'options' => ['Black and white', 'Color']],
        ['key' => 'paper_size', 'label' => 'Paper size', 'type' => 'select', 'options' => ['A4', 'A3', 'Letter']],
        ['key' => 'binding', 'label' => 'Binding', 'type' => 'select', 'options' => ['None', 'Staple', 'Spiral', 'Hardcover']],
    ]],
    'Hair & Beauty' => ['fields' => [
        ['key' => 'service', 'label' => 'Service', 'type' => 'text'],
        ['key' => 'where', 'label' => 'Where', 'type' => 'select', 'options' => ['My place', 'Your place']],
    ]],
    'Repairs' => ['fields' => [
        ['key' => 'item', 'label' => 'Item to repair', 'type' => 'text'],
        ['key' => 'brand_model', 'label' => 'Brand / model', 'type' => 'text'],
    ]],
    'Laundry' => ['fields' => [
        ['key' => 'item_count', 'label' => 'Number of items', 'type' => 'text'],
        ['key' => 'service', 'label' => 'Service', 'type' => 'select', 'options' => ['Wash and fold', 'Dry clean', 'Iron only']],
    ]],
    'Errands & Delivery' => ['fields' => [
        ['key' => 'pickup', 'label' => 'Pickup location', 'type' => 'text'],
        ['key' => 'dropoff', 'label' => 'Drop-off location', 'type' => 'text'],
        ['key' => 'item', 'label' => 'Item to deliver', 'type' => 'text'],
        ['key' => 'item_cost_covered_by', 'label' => 'Who covers item cost', 'type' => 'select', 'options' => ['Poster reimburses', 'Included in gig amount']],
    ]],
    'Photography' => ['fields' => [
        ['key' => 'shoot_date', 'label' => 'Shoot date', 'type' => 'text'],
        ['key' => 'shoot_location', 'label' => 'Shoot location', 'type' => 'text'],
        ['key' => 'hours', 'label' => 'Hours needed', 'type' => 'text'],
        ['key' => 'deliverables', 'label' => 'Deliverables', 'type' => 'text'],
    ]],
    'Cash Agent (POS)' => ['fields' => [
        ['key' => 'transaction_type', 'label' => 'Transaction type', 'type' => 'select', 'options' => ['Withdrawal', 'Deposit', 'Transfer']],
        ['key' => 'amount_needed', 'label' => 'Amount needed', 'type' => 'text'],
    ]],
    'Fashion' => ['fields' => [
        ['key' => 'item', 'label' => 'Item', 'type' => 'text'],
        ['key' => 'size', 'label' => 'Size', 'type' => 'text'],
        ['key' => 'style_notes', 'label' => 'Style notes', 'type' => 'text'],
    ]],
    'Electronics' => ['fields' => [
        ['key' => 'device', 'label' => 'Device', 'type' => 'text'],
        ['key' => 'issue', 'label' => 'Issue', 'type' => 'text'],
    ]],
    'Books' => ['fields' => [
        ['key' => 'title_subject', 'label' => 'Title / subject', 'type' => 'text'],
        ['key' => 'format', 'label' => 'Format', 'type' => 'select', 'options' => ['Physical', 'PDF / ebook']],
    ]],
    'Food' => ['fields' => [
        ['key' => 'cuisine_dish', 'label' => 'Cuisine / dish', 'type' => 'text'],
        ['key' => 'servings', 'label' => 'Servings', 'type' => 'text'],
        ['key' => 'dietary_notes', 'label' => 'Dietary notes', 'type' => 'text'],
    ]],
    'Other' => ['fields' => []],
];
