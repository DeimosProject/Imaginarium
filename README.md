# Imaginarium

**required**
```bash
php extension gearman
php extension sqlite (OR mysql)
```

```bash
mkdir assets/compile
chmod 0777 assets/compile
mkdir storage
chmod 0777 storage
chmod 0644 file.db (for sqlite)
```

**Callback response examples:**
```json
{
  "status": "ok",
  "fileSize": "3402794",
  "sizes": {
    "width": "5000",
    "height": "3125"
  },
  "mime": "image\/jpeg",
  "channels": "3",
  "hash": "81Xoxt",
  "user": "default",
  "data": {
    "Filename": "far-kray-4-5000x3125-gimalai-gori-shuter-2615.jpg",
    "Upload": "Submit Query"
  },
  "query": {
    "q": "api\/upload\/default",
    "id": "452"
  }
}
```

```json
{
  "status": "error"
}
```
