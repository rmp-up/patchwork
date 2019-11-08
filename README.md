# patchwork

> Supports the "patchwork" package-type in composer

**Please use the patch packages directly!**


## Usage

1. Choose a patch here: https://packagist.org/packages/patch/work/dependents
2. Add it to requirements
3. Set path to patch mapping (in "extra" - "patchwork" section)
4. `composer install`
5. Profit


Example:

```json
{
  "name": "myown/thing",
  "require": {
    "johnpbloch/wordpress": "5.2.*",
    "patch/wp-remove-hello": "0.1.*"
  },
  "extra": {
    "patchwork": {
      "wordpress/": "patch/wp-remove-hello"
    }
  }
}
``` 


The `extra.patchwork` section can also look like this:

```json
  "extra": {
    "patchwork": {

      "src/": "patch/wp-*",

      "some/path/": [
        "patch/wp-*",
        "patch/mage-*",
        "myown/patch",
      ]

    }
  }
```

