# Plugin Jeedom Tydom Client
Plugin Jeedom pour communiquer avec la box Tydom (Delta Dore)

Le plugin utilise un démon en nodeJs.
Ce démon permet:
- De récupérer les données de la box domotique (En Websocket).
- De lancer un serveur web (express) pour pouvoir accéder aux données via des requetes http.

Une gestion des dépendances permet d'installer tous les packages nécessaire au fonctionnement du plugin.

Ce plugin fonctionne uniquement avec les thermostats DeltaDore (Je n'ai que ça).

### Credits

- [cth35/tydom_python](https://github.com/cth35/tydom_python) pour le client nodeJs

## License

```
The MIT License

Copyright (c) 2019 Olivier Louvignes <olivier@mgcrea.io>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```
