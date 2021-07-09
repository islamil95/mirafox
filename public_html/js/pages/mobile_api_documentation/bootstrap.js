require('appJs/bootstrap.js');

const SwaggerUI = require('swagger-ui');

SwaggerUI({
    dom_id: '#swagger-ui',
    url: window.openApiOutputFile,
    defaultModelsExpandDepth: -1
});