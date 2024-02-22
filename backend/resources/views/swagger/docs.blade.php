<!DOCTYPE html>
<html>

<head>
    <title>API Мой гараж</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/api-docs/swagger-ui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/api-docs/index.css') }}" />
    <script src="/path/to/swagger-scripts.js"></script>
    @if (!empty($site_favicon))
        <link rel="icon" href="{{ $site_favicon['img_url'] }}" type="image/png">
    @endif
</head>

<body>
    <!-- Вставьте элемент, в котором будет отображаться документация -->
    <div id="swagger-ui"></div>
    <script src="{{ asset('assets/api-docs/swagger-ui-bundle.js') }}" charset="UTF-8"></script>
    <script src="{{ asset('assets/api-docs/swagger-ui-standalone-preset.js') }}" charset="UTF-8"></script>
    <script src="{{ asset('assets/api-docs/swagger-initializer.js') }}" charset="UTF-8"></script>

    <script>
        // Инициализация Swagger UI
        window.onload = function() {
            SwaggerUIBundle({
                url: "{{ asset('assets/api-docs/api-docs.json') }}", // Укажите путь к вашему файлу Swagger JSON
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>

</html>
