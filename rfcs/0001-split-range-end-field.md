# RFC 0001: Split range picker across two form fields

| Field | Value |
|-------|-------|
| **Status** | Implemented |
| **Author** | @coolsam726 |
| **Created** | 2026-06-13 |
| **Implemented in** | [#153](https://github.com/coolsam726/flatpickr/pull/153) |

## Summary

Add an optional `rangeEnd()` API so a single range Flatpickr field can read and write two separate model attributes (for example `starts_at` and `ends_at`) instead of storing an array on one field.

## Motivation

Today, `->rangePicker()` binds to one state path and dehydrates to an **array of two date strings**:

```php
Flatpickr::make('booking_period')
    ->rangePicker()
    ->format('Y-m-d');

// Saved state: ['2024-06-01', '2024-06-15']
```

That works for JSON columns or virtual attributes, but most Laravel models use **separate columns** for start and end dates. Developers then either:

- store JSON in one column (poor for querying/indexing), or
- manually split/join range state in form callbacks (repetitive and error-prone).

We want one visible range picker while persisting to two first-class attributes with no boilerplate.

## Goals

- Keep the existing single-field array behaviour as the **default** (no breaking change).
- Allow mapping a range picker to a second sibling state path via a fluent API.
- Hydrate from two DB values when editing; dehydrate back to two values on save.
- Keep the end field updated **live** so validation and reactive fields on `ends_at` work before save.
- Require **no JavaScript changes** — Flatpickr still manages one combined range string internally.

## Non-goals

- Two visible Flatpickr inputs for one range.
- Automatic creation of a hidden `ends_at` field in the schema (developers own their model/fillable attributes).
- Range-end splitting for `multiplePicker()` (out of scope for this RFC).

## Proposed API

```php
Flatpickr::make('starts_at')
    ->label('Event dates')
    ->rangePicker()
    ->rangeEnd('ends_at')
    ->format('Y-m-d');
```

| Method | Description |
|--------|-------------|
| `rangeEnd(string \| Closure \| null $field)` | Relative state path of the end date field (sibling by default). Supports closures for dynamic paths. |
| `getRangeEndField()` | Resolved end field name. |
| `getRangeEndStatePath()` | Absolute state path used during dehydration. |
| `hasRangeEndField()` | Whether split dehydration is active. |

### Behaviour

#### Livewire state (while editing)

| Path | Value |
|------|-------|
| `starts_at` | Combined range string for the picker, e.g. `2024-06-01 to 2024-06-15` |
| `ends_at` | End date only, synced via `afterStateUpdated` |

The picker remains entangled to `starts_at`. The combined string is required so Flatpickr can render the selection.

#### Hydration (load record)

When both `starts_at` and `ends_at` are filled and `starts_at` is **not** already a combined range string, merge them into one range display value for the picker.

When `starts_at` already contains the configured `rangeSeparator`, treat it as a combined range (legacy / single-column data).

#### Dehydration (save)

Override `getStateToDehydrate()` to return **two** keys:

```php
[
    'starts_at' => '2024-06-01',
    'ends_at'   => '2024-06-15',
]
```

Uses existing `dehydrateFlatpickr()` parsing (separator, regex, brute-force split).

Blank picker state clears both paths.

#### Validation

Existing range validation on the primary field still validates the combined / array value. Rules on `ends_at` (e.g. `after:starts_at`) work because the end field is synced live.

## Alternatives considered

### 1. Two separate date fields with linked min/max

```php
Flatpickr::make('starts_at')->maxDate(fn (Get $get) => $get('ends_at')),
Flatpickr::make('ends_at')->minDate(fn (Get $get) => $get('starts_at')),
```

**Rejected:** Two pickers, poor UX for selecting a range, no shared calendar range selection.

### 2. Custom `afterStateUpdated` in every resource

**Rejected:** Repetitive; easy to forget hydration on edit.

### 3. Store JSON array on one column

**Rejected:** Already supported by default range picker; doesn't solve separate-column models.

### 4. `rangePicker(end: 'ends_at')` parameter

**Rejected for now:** Prefer explicit `->rangeEnd()` chain for readability and optional Closure support.

## Drawbacks

- Developers must **not** add a second visible Flatpickr on `ends_at` (would conflict).
- Live form state temporarily holds a combined string on `starts_at` until save; code reading `$record->starts_at` mid-request should use dehydrated data.
- Nested paths rely on Filament's relative state path resolution; deeply nested schemas need careful field naming.

## Implementation plan

- [x] Add `$rangeEndField` property and fluent `rangeEnd()` method.
- [x] Override `getStateToDehydrate()` for split dehydration.
- [x] Extend `afterStateHydrated()` to merge separate DB values on load.
- [x] Add `afterStateUpdated()` + `syncRangeEndField()` for live end sync.
- [x] Pest tests in `FlatpickrRangeEndTest.php`.
- [x] README documentation.
- [x] Close this RFC as **Implemented** (PR linked below).

## Open questions

_None — RFC closed; implemented as described above._
