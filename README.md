## Explicación de la Solución

La aplicación se ha desarrollado siguiendo un patrón de arquitectura que promueve la separación de responsabilidades y la mantenibilidad. A continuación, se detallan los aspectos más relevantes de la solución:

* **Capa de Servicio:** La capa de servicio se compone de varios servicios especializados:
    * `ChuckNorrisApiService`: Responsable de la comunicación directa con la API de Chuck Norris, incluyendo la gestión de reintentos y límites de tasa.
    * `SearchCacheService`: Encargado de la lógica de almacenamiento y recuperación de datos en caché utilizando Redis.
    * `SearchRecordService`: Dedicado a la gestión del guardado de la información de búsqueda en la base de datos, utilizando un sistema de colas.
    * `ChuckNorrisSearchService` (Orquestador): Actúa como punto de entrada principal para la lógica de búsqueda, coordinando las interacciones entre los demás servicios.
    * `LocaleService`: Gestiona la configuración del idioma de la aplicación, permitiendo establecer la localización.

* **Capa de Repositorio:** Para la interacción con la base de datos, se utiliza un patrón de repositorio `EloquentSearchRepository` implementando una interfaz `SearchRepositoryInterface`. Esto proporciona una abstracción sobre la forma en que se acceden y manipulan los datos, facilitando la posibilidad de cambiar la implementación de la base de datos en el futuro. La inyección de dependencias se utiliza para desacoplar el controlador y los servicios de las implementaciones concretas de los repositorios.

* **Caché:** Para mejorar el rendimiento y reducir la carga en la API externa, se ha implementado un sistema de caché con Redis. Los resultados de las búsquedas (especialmente las búsquedas por categoría y quizás hechos aleatorios) se almacenan en caché durante un tiempo determinado. Esto permite que las solicitudes posteriores con los mismos parámetros se sirvan directamente desde la caché, sin necesidad de volver a consultar la API.

* **Sistema de Reintento para la API:** Aunque la API de Chuck Norris no muestra explícitamente límites de tasa en sus encabezados, se ha implementado un sistema de reintento para las llamadas a la API. Esto ayuda a garantizar la robustez de la aplicación al manejar posibles problemas de conexión temporales o la imposición de límites de tasa no anunciados. En caso de un fallo en la solicitud inicial, la aplicación intentará realizar la misma solicitud un número limitado de veces después de un breve período de espera incrementado.

* **Guardado en Base de Datos con Jobs y Queues:** Cada vez que se realiza una búsqueda, los detalles de la búsqueda y los resultados obtenidos se guardan en la base de datos de forma asíncrona utilizando Jobs ( `SaveSearchJob`) y el sistema de colas de Redis. Para la gestión de la cola de trabajos, se utiliza una interfaz `JobDispatcherInterface` con una implementación concreta para el sistema de colas de Laravel. Esto asegura que la respuesta al usuario sea rápida, ya que la tarea de guardar en la base de datos se delega a un proceso en segundo plano.

* **Envío de Correo Electrónico con Jobs y Queues:** Cuando el usuario proporciona una dirección de correo electrónico, los resultados de la búsqueda se envían por correo electrónico también de forma asíncrona utilizando Jobs (por ejemplo, `SendSearchResultsEmail`) y el sistema de colas de Redis. Esto garantiza que el envío de correos electrónicos no bloquee la experiencia del usuario y permite manejar un gran volumen de solicitudes de envío de correo electrónico de manera eficiente. Se ha implementado un servicio de notificación (`EmailService` con su interfaz `NotificationServiceInterface`) para abstraer la lógica de envío de correos electrónicos. **El enlace incluido en el correo electrónico se genera idealmente a partir de la información almacenada en la caché o la base de datos. Actualmente, se obtiene de la caché o directamente de la API.**

* **Controladores:** El controlador `SearchController` es responsable de recibir las peticiones del usuario, invocar los servicios correspondientes para realizar la lógica de negocio y devolver las respuestas al usuario.

* **Middleware para la Gestión del Idioma:** Se ha implementado un middleware personalizado llamado `LocaleMiddleware` para gestionar el idioma de la aplicación. Establece el idioma para la aplicación y se guarda en la sesión del usuario.

* **Registro (Logging) con Interfaz:** Para el registro de eventos y errores, la aplicación utiliza la inyección de la interfaz `Psr\Log\LoggerInterface` en lugar de un facade estático. Esto facilita la prueba y el desacoplamiento de la implementación de logging.

* **Seguimiento de Búsquedas Iniciadas:** Para evitar el despacho duplicado del job de guardado de búsqueda dentro de la misma petición, se utiliza un mecanismo de seguimiento a nivel de la petición, como los atributos del objeto Request.

* **Chiste Aleatorio en la Página de Inicio:** En la página de inicio (index), se muestra un chiste aleatorio. Este chiste se obtiene de una lista de resultados almacenada en caché. La lista se genera llamando al servicio de búsqueda con `type = keyword` y `query = 'Chuck'`, lo que devuelve una extensa lista de aproximadamente 10.000 elementos. Los resultados de esta llamada a la API se almacenan en caché durante 1 hora. Cada vez que se recarga la página de inicio, se selecciona y muestra un chiste diferente de esta lista en caché.

* **Consideraciones de SEO:** Con el objetivo de optimizar el SEO (Search Engine Optimization), se ha optado por incluir el idioma directamente en la URL de la aplicación. Esto permite a los motores de búsqueda identificar y categorizar el contenido por idioma de manera más efectiva.

* **Infraestructura:** Para facilitar el desarrollo y despliegue, la aplicación se ha configurado para ejecutarse utilizando **Docker Compose**, con **Redis** para la gestión de la caché y las colas, y **MySQL** como base de datos principal.

* **Frontend:** La interfaz de usuario de la aplicación se ha construido utilizando **Laravel Blade templates** para la generación del HTML en el servidor y **jQuery** para mejorar la interactividad y la manipulación del DOM en el lado del cliente. Esta elección se realizó para demostrar la compatibilidad con las tecnologías frontend utilizadas por la empresa y como una demostración de habilidades en estas herramientas.

En resumen, la solución se basa en principios de diseño robustos, utilizando patrones como Servicios y Repositorios, inyección de dependencias para la flexibilidad, caché para el rendimiento y colas con Jobs para el procesamiento en segundo plano de tareas como el guardado en la base de datos y el envío de correos electrónicos.

**Nota Adicional:**

Se incluye una carpeta llamada `Screenshots` en la raíz del repositorio, donde se pueden encontrar pantallazos de la aplicación para una mejor comprensión visual de su funcionamiento.

## Instrucciones de Instalación

Esta aplicación está configurada para ejecutarse utilizando **Laravel Sail**, que proporciona un entorno de desarrollo Docker listo para usar.

1.  **Requisitos Previos:**
    * Docker (https://www.docker.com/get-started/) y Docker Compose (generalmente incluido con Docker Desktop) instalados.

2.  **Pasos de Instalación:**
    * Clona el repositorio:
        ```bash
        git clone <URL_de_tu_repositorio>
        cd ChuckNorris
        ```
    * Copia el archivo de entorno:
        ```bash
        cp .env.example .env
        ```
        Edita el archivo `.env` para configurar las variables de entorno. Asegúrate de configurar las credenciales de **Mailtrap** para las pruebas de correo electrónico:
        ```
        MAIL_MAILER=smtp
        MAIL_HOST=smtp.mailtrap.io
        MAIL_PORT=2525
        MAIL_USERNAME=YOUR_MAILTRAP_USERNAME
        MAIL_PASSWORD=YOUR_MAILTRAP_PASSWORD
        MAIL_ENCRYPTION=tls
        MAIL_FROM_ADDRESS=tu@email.com (opcional)
        ```
        Reemplaza `YOUR_MAILTRAP_USERNAME` y `YOUR_MAILTRAP_PASSWORD` con tus credenciales de Mailtrap.
    * Inicia el entorno de desarrollo con Sail:
        ```bash
        ./vendor/bin/sail up -d
        ```
        Este comando construirá e iniciará los contenedores de Docker para la aplicación, la base de datos MySQL y Redis.
    * Ejecuta las migraciones de la base de datos:
        ```bash
        ./vendor/bin/sail artisan migrate
        ```
    * Genera la clave de la aplicación:
        ```bash
        ./vendor/bin/sail artisan key:generate
        ```
    * Inicia el worker de la cola para procesar tareas en segundo plano (guardado en base de datos y envío de correos electrónicos):
        ```bash
        ./vendor/bin/sail artisan queue:work --redis
        ```

3.  **Acceder a la Aplicación:**
    Una vez que los contenedores estén en funcionamiento, la aplicación estará accesible en tu navegador en `http://localhost`.



## Modelo de Datos

La aplicación utiliza las siguientes tablas para almacenar información:

* **searches:** Guarda la consulta de búsqueda (`query`) que puede ser una categoría o una palabra clave, el tipo de búsqueda (`type`) que puede ser 'keyword', 'category' o 'random', los resultados obtenidos de la búsqueda almacenados en formato JSON (`results`), y la dirección de correo electrónico (`email`) proporcionada por el usuario (si la hay).
* `id` (INT, clave primaria)
* `type` (VARCHAR) - El tipo de búsqueda (keyword, category, random).
* `query` (TEXT, nullable) - La consulta de búsqueda (palabra clave o categoría).
* `results` (TEXT) - Los resultados de la búsqueda en formato JSON.
* `email` (VARCHAR, nullable) - La dirección de correo electrónico del usuario (opcional).
* `created_at` (TIMESTAMP)
* `updated_at` (TIMESTAMP)

## Testing

Implementar un conjunto de pruebas unitarias y de integración para cubrir algunas de las funcionalidades de la aplicación, incluyendo pruebas para los controladores, servicios, repositorios, jobs y middleware.
   
 * `SearchResultsMailFeatureTest`
 * `SendSearchResultsEmailTest`
 * `SearchResultsMailTest`
 * `EloquentSearchRepositoryTest`
 * `SearchControllerTest`



## Mejoras Adicionales

* **Mostrar el Número Total de Resultados en el Correo Electrónico:** Incluir en el correo electrónico enviado al usuario el número total de resultados encontrados para su búsqueda.
* **Cambiar el Número de Resultados Mostrados en la UI:** Permitir al usuario configurar o cambiar el número de resultados que se muestran por página en la interfaz de usuario.
* **Utilizar un Enumerado (Enum) para el Tipo de Búsqueda:** Reemplazar las cadenas de texto codificadas (`keyword`, `category`, `random`) utilizadas para el tipo de búsqueda con un enumerado para mejorar la legibilidad y evitar errores tipográficos.
* **Aumentar el Tiempo de Caché:** El tiempo de caché actual está configurado en 1 hora, pero podría aumentarse significativamente ya que el contenido de la API no parece cambiar con mucha frecuencia. Considerar un tiempo de caché más prolongado para mejorar aún más el rendimiento.
* **Testing Completo:** Implementar una cobertura exhaustiva de pruebas unitarias, de integración y funcionales para asegurar la robustez de todas las funcionalidades de la aplicación.
* **Generación de Enlaces en Correos:** Actualmente, los correos electrónicos dependen directamente de la API o de la caché. Para asegurar una mayor robustez se deberían generar basándonos en la información almacenada en la base de datos.