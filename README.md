# mensa-parser

Parses the stw-bremen.de mensa pages written in PHP.

## Output

### Default

```
parse_menu(...)
```

```
[
  timestamp => [
    [
      "name" => category_name,
      "meals" => [
        meal_description,
        ...
      ]
    ],
    ...
  ],
  ...
]
```

## Joined dishes

```
join_dishes(parse_menu(...))
```

```
[
  timestamp => [
    [
      "name" => category_name,
      "meal" => meal_description
    ],
    ...
  ],
  ...
]
```

## Legacy output

```
parse_mensa(..., null)
```
Same as `join_dishes(parse_menu(...))`.

```
parse_menu(..., day)
```

```
[
  [
    "name" => category_name,
    "meal" => meal_description
  ],
  ...
]
```
