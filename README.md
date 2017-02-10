# Imaginarium

**required**
```bash
php extension gearman
php extension sqlite (OR mysql)
```

```bash
mkdir assets/compile
chmod 0777 assets/compile
chmod 0777 storage
chmod 0644 file.db (for sqlite)
```

**Callback response examples:**
```
[
    "user": "default",
    "status": "ok",
    "time": [
        "download": "1486643290069",
        "finish": "1486643290569",
    },
    "id": "dAc93x",
    "size": [
        "width": 1300,
         "height": 600
    ]
]
```

```
[
    "user": "default",
    "status": "error",
    "time": [
        "download": "1486643290069",
        "finish": "1486643290569",
    }
]
```
