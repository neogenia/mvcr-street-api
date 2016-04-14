# MVCR Street API
Nette based MVCR Street API is used for obtain the streets by the city via REST API. Only for Czech Republic!

## Import data
- download xml file from [mvcr site](http://aplikace.mvcr.cz/adresy/) and copy to root of project
- run command:
```
php www/index.php import:address
```
## Usage
- add header `X-HTTP-Method-Override: GET`
- basic authentication: add custom header `X-Api-Key` with value from `config.local.neon`
- endpoint: `http://<project_url>/api/streets`

### **get streets by CityId (city part exclude):**
input:
```json
{
	"cityId": 1
}
```
output:
 ```json
{
    "streets": [
        {
            "streetId": 123,
            "title": "Resslova",
            "code": "21113"
        },
        {
            "streetId": 133,
            "title": "Gorkého",
            "code": "5970"
        }
    ]
}
```

### **get streets by CityId (city part include):**
input:
```json
{
	"cityId": 1,
	"includePartCities": true
}
```
output:
 ```json
{
    "partCities": [
        {
            "title": "Veveří",
            "code": "18141",
            "minZip": 61600,
            "maxZip": 61600,
            "streets": [
                {
                    "streetId": 123,
                    "title": "Resslova",
                    "code": "21113"
                },
                {
                    "streetId": 133,
                    "title": "Gorkého",
                    "code": "5970"
                }
         	]
        }
    ]
}
```

### **get streets by CityPartId:**
input:
```json
{
	"cityPartId": 1
}
```
output:
 ```json
{
    "streets": [
        {
            "streetId": 123,
            "title": "Resslova",
            "code": "21113"
        },
        {
            "streetId": 133,
            "title": "Gorkého",
            "code": "5970"
        }
    ]
}
```

### Querying cities:
- use the endpoint: http://<project_url>/api/cities
input:
```json
{
	"title": "Brno" // optional search key
}
```
output:
 ```json
{
    "cities": [
        {
            "cityId": 347,
            "title": "Brno",
            "code": "23964",
            "region": "Brno",
            "country": "Jihomoravský"
        },
        {
            "cityId": 282,
            "title": "Úsobrno",
            "code": "1097",
            "region": "Boskovice",
            "country": "Jihomoravský"
        },
        ...
    ]
}
