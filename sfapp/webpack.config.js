const Encore = require('@symfony/webpack-encore');

Encore
    // Le répertoire où les fichiers compilés seront stockés
    .setOutputPath('public/build/')
    // Le répertoire public où les assets sont accessibles
    .setPublicPath('/build')
    // Le fichier d'entrée de votre application (ex. assets/app.js)
    .addEntry('app', './assets/app.js')
    .enableStimulusBridge('./assets/controllers.json')
    .enableSassLoader()
    .enablePostCssLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableVersioning(Encore.isProduction()) // Optionnel pour les versions en production

    // Active le runtime chunk (recommandé)
    .enableSingleRuntimeChunk()

;

module.exports = Encore.getWebpackConfig();
