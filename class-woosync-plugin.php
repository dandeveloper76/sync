<?php
class WooSyncPlugin
{
    private $access_token = null;
    private $options = [];

    public function __construct()
    {
        $this->options = get_option('woosync_opciones', []);
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_init', [$this, 'register_settings']);


        if (isset($_POST['woosync_action'])) {
            switch ($_POST['woosync_action']) {
                case 'connect_api':
                    add_action('admin_notices', [$this, 'conectar_api']);
                    add_action('admin_notices', [$this, 'desconectar_api']);
                    break;
                case 'sync_products':
                    add_action('admin_notices', [$this, 'sync_products']);
                    break;
                case 'sync_wc_products':
                    add_action('admin_notices', [$this, 'sync_wc_products']);
                    break;
                case 'update_product':
                    add_action('admin_notices', [$this, 'update_product'],10,1);
                    break;    
            }
        }

    }

    // Método para ejecutar el plugin
    public function run()
    {

    }




    public function register_menus()
    {
        add_menu_page(
            'WooSync',
            'WooSync',
            'manage_options',
            'woosync',
            [$this, 'display_main_menu'],
            'dashicons-admin-generic',
            20
        );

        add_submenu_page(
            'woosync',
            'Configuración del API',
            'Configuración del API',
            'manage_options',
            'woosync-config',
            [$this, 'woosync_mostrar_configuracion']
        );

        add_submenu_page(
            'woosync',
            'Sincronizar Productos',
            'Sincronizar Productos',
            'manage_options',
            'woosync-sync-products',
            [$this, 'woosync_mostrar_sincronizacion']
        );
        add_submenu_page(
            'woosync',
            'Actualizar 1 Producto',
            'Actualizar 1 Producto',
            'manage_options',
            'woosync-sync-product',
            [$this, 'woosync_mostrar_product']
        );
        add_submenu_page(
            'woosync',
            'Logs',
            'Logs',
            'manage_options',
            'woosync-sync-logs',
            [$this, 'woosync_mostrar_logs']
        );
    }

    public function woosync_mostrar_configuracion()
    {
        ?>
        <div class="wrap">
            <h1>Configuración del API</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('woosync_opciones_grupo');
                do_settings_sections('woosync-config');
                submit_button();
                ?>
            </form>

            <form method="post" action="">
                <input type="hidden" name="woosync_action" value="connect_api" />
                <input type="submit" name="woosync_conectar_api" class="button-primary"
                    value="Conectar con la API">
            </form>
        </div>
        <?php
    }

    public function display_main_menu()
    {
        ?>
        <div class="wrap">
            <h1>Bienvenido a WooSync</h1>
            <p>Aquí puedes configurar las opciones de sincronización.</p>
        </div>
        <?php
    }

    // Registrar ajustes en la configuración
    public function register_settings()
    {
        register_setting('woosync_opciones_grupo', 'woosync_opciones');

        add_settings_section(
            'woosync_seccion',
            '',
            [$this, 'woosync_seccion_callback'],
            'woosync-config'
        );

        $fields = [
            'woosync_api_url' => 'URL de la API',
            'woosync_api_user' => 'Usuario',
            'woosync_api_password' => 'Contraseña',
            'woosync_api_connection' => 'ConnectionName'
        ];

        foreach ($fields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'render_setting_field'],
                'woosync-config',
                'woosync_seccion',
                ['key' => $key]
            );
        }
    }

    public function render_setting_field($args)
    {
        $value = esc_attr($this->options[$args['key']] ?? '');
        echo '<input type="text" name="woosync_opciones[' . $args['key'] . ']" value="' . $value . '" class="regular-text" />';
    }

    function woosync_seccion_callback()
    {
        echo '<p>Introduce la URL de la API y otras configuraciones necesarias.</p>';
    }

    public function woosync_mostrar_sincronizacion()
    {
        ?>
        <div class="wrap">
            <h1>Descargar Productos</h1>
            <p>Haga clic en el botón a continuación para sincronizar los productos con la API externa.</p>
            <form method="post" action="">
                <input type="hidden" name="woosync_action" value="sync_products" />
                <input type="submit" name="woosync_sync_button" class="button-primary"
                    value="Sincronizar Productos">
            </form>
        </div>
        <div class="wrap">
            <h1>Sincronizar Productos WC</h1>
            <p>Haga clic en el botón a continuación para sincronizar los productos en WooCommerce segun los
                datos insertados anteriormente</p>
            <form method="post" action="">
                <input type="hidden" name="woosync_action" value="sync_wc_products" />
                <input type="submit" name="woosync_sync_button" class="button-primary"
                    value="Sincronizar Productos">
            </form>
        </div>
        <?php
    }

    public function woosync_mostrar_logs()
    {
        ?>
        <div class="wrap">
            <h1>Logs</h1>
            <p>Registro de actividad de la API</p>

        </div>
        <?php
    }

    public function woosync_mostrar_product()
    {

        ?>
        <div class="wrap">
            <h1>Actualizar un producto</h1>
            <p>Sincronizar 1 producto individualmente</p>
        </div>
        <div class="wrap">           
            <p>Introduce el sku del producto a actualizar</p>
            <form method="post" action="">
                <input type="hidden" name="woosync_action" value="update_product" />
                <input type="text" name="woosync_sku"/>
                <input type="submit" name="woosync_sync_button" class="button-primary"
                    value="Actualizar Productos">
            </form>
        </div>
        <?php
    }



    public function conectar_api()
    {
        $resultado_login = $this->woosync_login_api();
        if (is_string($resultado_login)) {
            echo '<div class="error"><p>' . $resultado_login . '</p></div>';
        } else {
            $this->access_token = $resultado_login[0];
            echo '<div class="updated"><p>Login Correcto. Token: ' . $resultado_login[0] . '</p></div>';
        }

    }
    public function desconectar_api()
    {
        $resultado_logout = $this->woosync_logout_api();
        if (is_string($resultado_logout)) {
            echo '<div class="error"><p>' . $resultado_logout . '</p></div>';
        } else {
            echo '<div class="updated"><p>Logout Correcto</p></div>';
        }

    }

    // Métodos para hacer login y logout en la API
    function woosync_login_api()
    {
        $url = $this->options['woosync_api_url'] ?? '';
        $user = $this->options['woosync_api_user'] ?? '';
        $password = $this->options['woosync_api_password'] ?? '';
        $connection = $this->options['woosync_api_connection'] ?? '';



        if (empty($url) || empty($user) || empty($password) || empty($connection)) {
            return 'Faltan datos de configuración de la API.';
        }

        $login_url = "{$url}/login?UserName=" . urlencode($user) . "&Password=" . urlencode($password) . "&ConnectionName=" . urlencode($connection);

        $response = wp_remote_post($login_url, [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json', // Ajustar según sea necesario
            ],
        ]);

        if (is_wp_error($response)) {
            return 'Error de conexión: ' . $response->get_error_message();
        }        
        $response_headers = wp_remote_retrieve_headers($response);
        $response_body = wp_remote_retrieve_body($response);
        if (isset($response_headers['Access-Token'])) {
            $this->access_token = $response_headers['Access-Token'];
            return [$response_headers['Access-Token']];
        } else {            
            return 'Error de autenticación: ' . ($response_body ?? 'Desconocido');
        }
    }

    function woosync_logout_api()
    {
        $url = $this->options['woosync_api_url'] ?? '';

        $logout_url = "{$url}/logoff";
        $response = wp_remote_post($logout_url, [
            'method' => 'POST',
            'headers' => [
                'Access-Token' => $this->access_token,
            ],
            'timeout' => 100,
        ]);

        if (is_wp_error($response)) {
            return 'Error de conexión al hacer logout: ' . $response->get_error_message();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            return true;
        } else {
            return 'Error al hacer logout: Código de respuesta ' . $response_code;
        }
    }

    public function sync_products()
    {
        $top = 20000;
        $skip = 0;

        while (true) {
            $max_attempts = 5;  // Número máximo de intentos para obtener un nuevo token
            $attempt = 0;  // Contador de intentos
            $inital_token = $this->access_token;

            while ($attempt < $max_attempts) {
                $this->woosync_login_api();
                if ($inital_token !== $this->access_token) {
                    break;
                }

                $attempt++;
            }


            if (!$this->access_token) {
                echo '<div class="error"><p>No estás conectado a la API. Por favor, conecta la API primero.</p></div>';
                return;
            }

            $url = $this->options['woosync_api_url'] . "/select/Articulos?top=" . $top . "&skip=" . $skip;
            echo $url . '<br>';
            $response = wp_remote_get($url, [
                'headers' => [
                    'Access-Token' => $this->access_token,
                ]
            ]);

            if (is_wp_error($response)) {
                print_r($response);
                echo '<div class="error"><p>Error de conexión: ' . $response->get_error_message() . '</p></div>';
                return;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            if ($response_code === 200) {
                $data = json_decode(json_decode($body, true));
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo '<div class="error"><p>Error decodificando JSON: ' . json_last_error_msg() . '</p></div>';
                    return;
                }
                if (isset($data->data) && is_array($data->data)) {
                    foreach ($data->data as $product) {
                        $this->insert_product_into_db($product);
                        //$this->create_or_update_product($product);
                    }

                    if (count($data->data) < $top) {
                        break;
                    }

                    $skip += $top;
                } else {
                    echo '<div class="error"><p>Error: No se encontraron datos de productos en la respuesta de la API.</p></div>';
                    break;
                }

            } else {
                echo '<div class="error"><p>Error en la sincronización: Código de respuesta ' . $response_code . '</p></div>';
                break;
            }
            $this->woosync_logout_api();
        }

        echo '<div class="updated"><p>¡Productos sincronizados correctamente en la base de datos!</p></div>';

    }

    public function sync_wc_products()
    {
        // Recupera los productos desde la base de datos
        $products = $this->get_products_from_db();

        if (empty($products)) {
            echo '<div class="error"><p>No hay productos en la base de datos para sincronizar.</p></div>';
            return;
        }

        // Itera sobre cada producto y sincroniza con WooCommerce
        foreach ($products as $product) {
            $this->sync_with_woocommerce($product);
        }

        echo '<div class="updated"><p>¡Productos sincronizados correctamente en WooCommerce!</p></div>';
    }

    public function update_product($woosync_sku)
    {
        if ( isset( $_POST['woosync_sku'] )) {
            $code = $_POST['woosync_sku'];
        }        
        //check product in API
        $product_data = $this->check_API_product($code);
        if(isset($product_data)){
            //Update Product in DB
            $this->insert_product_into_db($product_data);
            //Update Product in WC
            $this->create_or_update_product($product_data);
            echo '<div class="updated"><p>¡Producto actualizado correctamente!</p></div>';    
        }
        
    }
 

    /**
     * Inserta los datos del producto en la tabla temporal.
     */
    public function insert_product_into_db($product)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_products';

        // Extraer los atributos del nombre del producto si están presentes.
        $product_name_parts = explode('/', $product->Producto);        
        // Asumimos que el nombre del producto tiene una estructura similar a "Proveedor/Marca/Modelo/Color" y extraemos estos valores
        $proveedor = count($product_name_parts) > 3 && isset($product_name_parts[0]) ? $product_name_parts[0] : null;
        $modelo = count($product_name_parts) > 3 && isset($product_name_parts[2]) ? $product_name_parts[2] : null;
        $color = count($product_name_parts) > 3 && isset($product_name_parts[3]) ? $product_name_parts[3] : null;
        $atributo = count($product_name_parts) > 3 && isset($product_name_parts[4]) ? $product_name_parts[4] : null;

        // Inserta o actualiza el producto basándose en el Código (si ya existe).
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table_name 
            (Codigo, Centro, Codigo_equivalente, CodigoArticulo_proveedor, Id_proveedor, NombreCorto_proveedor, NombreLargo_proveedor, Descripcion, Clase_producto, Familia_agrupacion1, Familia_agrupacion2, Familia_agrupacion3, Referencia_producto, Referencia_producto2, Producto, Marca, Lentes_Geometria, Lentes_Diametro, Lentes_Esfera, Lentes_Cilindro, Lentes_Eje, Lentes_Adicion, Lentillas_Esfera, Lentillas_Cilindro, Lentillas_Eje, Lentillas_Adicion, Lentillas_Radio, Lentillas_Radio2, Lentillas_CurvaPeriferica, Lentillas_Diametro, Gafas_Color, Gafas_TipoLente, Gafas_Calibre, Gafas_Puente, Gafas_Varilla, IVA, Precio_compra, Precio_venta, Precio_venta2, Existencias, ExistenciasTotales, FechaAlta, FechaBaja, UltimaActualizacion, UltimaEntrada, Proveedor, Modelo, Color,Atributo) 
            VALUES 
            (%s, %d, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %f, %f, %f, %f, %d, %d, %s, %s, %s, %s, %s, %s, %s,%s)
            ON DUPLICATE KEY UPDATE 
                Centro = VALUES(Centro),
                Codigo_equivalente = VALUES(Codigo_equivalente),
                CodigoArticulo_proveedor = VALUES(CodigoArticulo_proveedor),
                Id_proveedor = VALUES(Id_proveedor),
                NombreCorto_proveedor = VALUES(NombreCorto_proveedor),
                NombreLargo_proveedor = VALUES(NombreLargo_proveedor),
                Descripcion = VALUES(Descripcion),
                Clase_producto = VALUES(Clase_producto),
                Familia_agrupacion1 = VALUES(Familia_agrupacion1),
                Familia_agrupacion2 = VALUES(Familia_agrupacion2),
                Familia_agrupacion3 = VALUES(Familia_agrupacion3),
                Referencia_producto = VALUES(Referencia_producto),
                Referencia_producto2 = VALUES(Referencia_producto2),
                Producto = VALUES(Producto),
                Marca = VALUES(Marca),
                Lentes_Geometria = VALUES(Lentes_Geometria),
                Lentes_Diametro = VALUES(Lentes_Diametro),
                Lentes_Esfera = VALUES(Lentes_Esfera),
                Lentes_Cilindro = VALUES(Lentes_Cilindro),
                Lentes_Eje = VALUES(Lentes_Eje),
                Lentes_Adicion = VALUES(Lentes_Adicion),
                Lentillas_Esfera = VALUES(Lentillas_Esfera),
                Lentillas_Cilindro = VALUES(Lentillas_Cilindro),
                Lentillas_Eje = VALUES(Lentillas_Eje),
                Lentillas_Adicion = VALUES(Lentillas_Adicion),
                Lentillas_Radio = VALUES(Lentillas_Radio),
                Lentillas_Radio2 = VALUES(Lentillas_Radio2),
                Lentillas_CurvaPeriferica = VALUES(Lentillas_CurvaPeriferica),
                Lentillas_Diametro = VALUES(Lentillas_Diametro),
                Gafas_Color = VALUES(Gafas_Color),
                Gafas_TipoLente = VALUES(Gafas_TipoLente),
                Gafas_Calibre = VALUES(Gafas_Calibre),
                Gafas_Puente = VALUES(Gafas_Puente),
                Gafas_Varilla = VALUES(Gafas_Varilla),
                IVA = VALUES(IVA),
                Precio_compra = VALUES(Precio_compra),
                Precio_venta = VALUES(Precio_venta),
                Precio_venta2 = VALUES(Precio_venta2),
                Existencias = VALUES(Existencias),
                ExistenciasTotales = VALUES(ExistenciasTotales),
                FechaAlta = VALUES(FechaAlta),
                FechaBaja = VALUES(FechaBaja),
                UltimaActualizacion = VALUES(UltimaActualizacion),
                UltimaEntrada = VALUES(UltimaEntrada),
                Proveedor = VALUES(Proveedor),
                Modelo = VALUES(Modelo),
                Color = VALUES(Color),
                Atributo = VALUES(Atributo)
            ",
                $product->Codigo,
                $product->Centro,
                $product->Codigo_equivalente,
                $product->CodigoArticulo_proveedor,
                $product->Id_proveedor,
                $product->NombreCorto_proveedor,
                $product->NombreLargo_proveedor,
                $product->Descripcion,
                $product->Clase_producto,
                $product->Familia_agrupacion1,
                $product->Familia_agrupacion2,
                $product->Familia_agrupacion3,
                $product->Referencia_producto,
                $product->Referencia_producto2,
                $product->Producto,
                $product->Marca,
                $product->Lentes_Geometria,
                $product->Lentes_Diametro,
                $product->Lentes_Esfera,
                $product->Lentes_Cilindro,
                $product->Lentes_Eje,
                $product->Lentes_Adicion,
                $product->Lentillas_Esfera,
                $product->Lentillas_Cilindro,
                $product->Lentillas_Eje,
                $product->Lentillas_Adicion,
                $product->Lentillas_Radio,
                $product->Lentillas_Radio2,
                $product->Lentillas_CurvaPeriferica,
                $product->Lentillas_Diametro,
                $product->Gafas_Color,
                $product->Gafas_TipoLente,
                $product->Gafas_Calibre,
                $product->Gafas_Puente,
                $product->Gafas_Varilla,
                $product->IVA,
                $product->Precio_compra,
                $product->Precio_venta,
                $product->Precio_venta2,
                $product->Existencias,
                $product->ExistenciasTotales,
                !empty($product->FechaAlta) ? date('Y-m-d H:i:s', strtotime($product->FechaAlta)) : null,
                !empty($product->FechaBaja) ? date('Y-m-d H:i:s', strtotime($product->FechaBaja)) : null,
                !empty($product->UltimaActualizacion) ? date('Y-m-d H:i:s', strtotime($product->UltimaActualizacion)) : null,
                !empty($product->UltimaEntrada) ? date('Y-m-d H:i:s', strtotime($product->UltimaEntrada)) : null,
                $proveedor, // Extraído del nombre
                $modelo, // Extraído del nombre
                $color,
                $atributo,
            )
        );
    }


    /**
     * Formatea el nombre del producto padre según el tipo.
     *
     * @param object $product Los datos del producto de la API.
     * @return string El nombre formateado del producto padre.
     */
    private function format_product_name($product)
    {
        if ($product->Clase_producto === 'G') {
            return "{$product->Marca} - {$product->Modelo}";
        }

        return $product->Producto;
    }
    /**
     * Sincroniza un producto con WooCommerce, creando o actualizando productos variables si es necesario.
     *
     * @param object $product El producto a sincronizar desde la API.
     */
    /**
     * Sincroniza un producto con WooCommerce, creando o actualizando productos variables si es necesario.
     *
     * @param object $product El producto a sincronizar desde la API.
     */
    private function sync_with_woocommerce($product)
    {
        // Determinar si el producto es variable
        $is_variable = (!empty($product->Color) || !empty($product->Atributo));

        // Formatear el nombre del producto correctamente según su tipo
        $product_name = $this->format_product_name($product);

        // Intentar obtener el ID del producto de WooCommerce si ya existe
        $existing_product_id = wc_get_product_id_by_sku($product->Codigo);

        // Si el producto ya existe, obtenemos su instancia, si no, creamos uno nuevo
        if ($existing_product_id) {
            $wc_product = wc_get_product($existing_product_id);
        } else {
            // Crear el producto en función de si es variable o simple
            $wc_product = $is_variable ? new WC_Product_Variable() : new WC_Product_Simple();
        }

        // Configuración básica del producto
        $wc_product->set_name($product_name);
        $wc_product->set_sku($product->Codigo);
        $wc_product->set_regular_price((string) $product->Precio_venta);
        $wc_product->set_description($product->Descripcion);
        $wc_product->set_stock_quantity($product->Existencias);
        $wc_product->set_manage_stock(true);

        // Guardar producto si es simple
        if (!$is_variable) {
            $wc_product->save();
        } else {
            // Guardar producto variable y crear sus variaciones
            $wc_product->save(); // Guardamos primero para obtener un ID si es nuevo
            $this->create_product_variations($wc_product->get_id(), $product);
        }
    }


    /**
     * Crea o actualiza variaciones para un producto variable en WooCommerce.
     *
     * @param int $product_id El ID del producto variable en WooCommerce.
     * @param object $product Los datos del producto desde la API.
     */
    private function create_product_variations($product_id, $product)
    {
        // Asegurarse de que WooCommerce tiene los atributos registrados
        $attributes = [];

        if (!empty($product->Color)) {
            $attributes['pa_color'] = $product->Color;
        }
        if (!empty($product->Atributo)) {
            $attributes['pa_atributo'] = $product->Atributo;
        }

        // Obtener el producto de WooCommerce
        $wc_product = wc_get_product($product_id);

        if (!$wc_product || $wc_product->get_type() !== 'variable') {
            // Si no es un producto variable, crear uno
            $wc_product = new WC_Product_Variable($product_id);
            $wc_product->save();
        }

        // Modificar SKU del producto padre si es variable
        if ($wc_product->get_type() === 'variable') {
            // Añadir "V" al final del SKU del producto padre para hacerlo único
            $sku_padre = $product->Codigo . 'V';
            $wc_product->set_sku($sku_padre);
        } else {
            // Si no es variable, mantener el SKU original
            $sku_padre = $product->Codigo;
            $wc_product->set_sku($sku_padre);
        }

        // Guardar el producto padre con el nuevo SKU
        $wc_product->save();

        // Configurar los atributos del producto variable
        if (!empty($attributes)) {
            $wc_attributes = [];
            foreach ($attributes as $attribute_name => $attribute_value) {
                $wc_attribute = new WC_Product_Attribute();
                $wc_attribute->set_name($attribute_name);
                $wc_attribute->set_options([$attribute_value]);
                $wc_attribute->set_visible(true);
                $wc_attribute->set_variation(true);
                $wc_attributes[] = $wc_attribute;
            }
            $wc_product->set_attributes($wc_attributes);
            $wc_product->save();
        }

        // Preparar los argumentos para la nueva variación
        $variation_args = [
            'post_title' => $product->Descripcion,
            'post_content' => '',
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type' => 'product_variation',
        ];

        // Verificar si el SKU ya existe para evitar duplicados
        $existing_variation_id = wc_get_product_id_by_sku($product->Codigo);
        if ($existing_variation_id) {
            // Si el SKU ya existe, no crear una nueva variación o mostrar un error
            return new WP_Error('sku_duplicate', 'El SKU ' . $product->Codigo . ' ya está en uso.');
        }

        // Crear la variación si no existe
        $variation_id = wp_insert_post($variation_args);
        $variation = new WC_Product_Variation($variation_id);

        // Crear un SKU único para la variación
        $unique_sku = $product->Codigo . '-' . $product->Color . '-' . $product->Modelo;  // Generar SKU único

        // Configurar la variación con el SKU único, precio, inventario y atributos
        $variation->set_sku($unique_sku);
        $variation->set_regular_price((string) $product->Precio_venta);
        $variation->set_stock_quantity($product->Existencias);
        $variation->set_manage_stock(true);

        // Configurar los atributos de la variación
        $variation_attributes = [];
        foreach ($attributes as $attribute_name => $attribute_value) {
            $variation_attributes["attribute_$attribute_name"] = $attribute_value;
        }
        $variation->set_attributes($variation_attributes);

        // Guardar la variación
        $variation->save();
    }


    /**
     * Recupera los productos de la tabla wp_api_products para sincronizarlos con WooCommerce.
     *
     * @return array Lista de productos como objetos.
     */
    private function get_products_from_db()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'api_products';

        // Realiza la consulta para recuperar todos los productos
        $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 100");

        return $results ? $results : [];
    }

    public function sync_products_old()
    {
        $this->woosync_login_api();

        if (!$this->access_token) {
            echo '<div class="error"><p>No estás conectado a la API. Por favor, conecta la API primero.</p></div>';
            return;
        }

        $top = 1000;
        $skip = 0;

        while (true) {
            $url = $this->options['woosync_api_url'] . "/select/Articulos?top=" . $top . "&skip=" . $skip;

            $response = wp_remote_get($url, [
                'headers' => [
                    'Access-Token' => $this->access_token,
                ]
            ]);

            if (is_wp_error($response)) {
                echo '<div class="error"><p>Error de conexión: ' . $response->get_error_message() . '</p></div>';
                return;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            if ($response_code === 200) {
                $data = json_decode(json_decode($body, true));
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo '<div class="error"><p>Error decodificando JSON: ' . json_last_error_msg() . '</p></div>';
                    return;
                }

                if (isset($data->data) && is_array($data->data)) {

                    foreach ($data->data as $product) {
                        $this->create_or_update_product($product);
                    }

                    if (count($data->data) < $top) {
                        break;
                    }

                    $skip += $top;
                } else {
                    echo '<div class="error"><p>Error: No se encontraron datos de productos en la respuesta de la API.</p></div>';
                    break;
                }

            } else {
                echo '<div class="error"><p>Error en la sincronización: Código de respuesta ' . $response_code . '</p></div>';
                break;
            }
        }

        echo '<div class="updated"><p>¡Productos sincronizados correctamente!</p></div>';
        $this->woosync_logout_api();
    }

    public function create_or_update_product($product_data)
    {
        $existing_product_id = $this->find_product_by_code($product_data->Codigo);
        if ($existing_product_id) {
            $this->update_existing_product($existing_product_id, $product_data);
        } else {
            $this->create_new_product($product_data);
        }
    }

    private function find_product_by_code($code)
    {
        $args = [
            'meta_key' => '_Codigo',
            'meta_value' => $code,
            'post_type' => 'product',
            'fields' => 'ids',
            'limit' => 1,
        ];

        $products = get_posts($args);

        return !empty($products) ? $products[0] : null;
    }

    private function create_new_product($product_data)
    {
        $product = new WC_Product_Simple(); // Crear un producto simple (puedes usar otros tipos si lo necesitas)

        $product->set_name($product_data->Producto);
        $product->set_regular_price($product_data->Precio_venta);
        $product->set_sku($product_data->Codigo);
        $product->set_stock_quantity($product_data->Existencias);
        $product->set_description($product_data->Descripcion);
        $product->set_manage_stock(true);
        $product->set_tax_status('taxable');
        if (isset($product_data->IVA)) {
            if ($product_data->IVA == 10) {
                $product->set_tax_class('IVA 10');
            } elseif ($product_data->IVA == 21) {
                $product->set_tax_class('IVA 21');
            } else {
                $product->set_tax_class('');
            }
        }


        $product_id = $product->save();

        update_post_meta($product_id, '_Codigo', $product_data->Codigo);
        update_post_meta($product_id, '_Referencia_producto', $product_data->Referencia_producto);
        update_post_meta($product_id, '_Marca', $product_data->Marca);
        update_post_meta($product_id, '_UltimaActualizacion', $product_data->UltimaActualizacion);

        $lentes = array(
            '_Lentes_Geometria' => $product_data->Lentes_Geometria,
            '_Lentes_Diametro' => $product_data->Lentes_Diametro,
            '_Lentes_Esfera' => $product_data->Lentes_Esfera,
            '_Lentes_Cilindro' => $product_data->Lentes_Cilindro,
            '_Lentes_Eje' => $product_data->Lentes_Eje,
            '_Lentes_Adicion' => $product_data->Lentes_Adicion
        );

        foreach ($lentes as $meta_key => $value) {
            update_post_meta($product_id, $meta_key, $value);
        }

        $lentillas = array(
            '_Lentillas_Esfera' => $product_data->Lentillas_Esfera,
            '_Lentillas_Cilindro' => $product_data->Lentillas_Cilindro,
            '_Lentillas_Eje' => $product_data->Lentillas_Eje,
            '_Lentillas_Adicion' => $product_data->Lentillas_Adicion,
            '_Lentillas_Radio' => $product_data->Lentillas_Radio,
            '_Lentillas_Radio2' => $product_data->Lentillas_Radio2,
            '_Lentillas_CurvaPeriferica' => $product_data->Lentillas_CurvaPeriferica,
            '_Lentillas_Diametro' => $product_data->Lentillas_Diametro
        );

        foreach ($lentillas as $meta_key => $value) {
            update_post_meta($product_id, $meta_key, $value);
        }

        $gafas = array(
            '_Gafas_Color' => $product_data->Gafas_Color,
            '_Gafas_TipoLente' => $product_data->Gafas_TipoLente,
            '_Gafas_Calibre' => $product_data->Gafas_Calibre,
            '_Gafas_Puente' => $product_data->Gafas_Puente,
            '_Gafas_Varilla' => $product_data->Gafas_Varilla
        );

        foreach ($gafas as $meta_key => $value) {
            update_post_meta($product_id, $meta_key, $value);
        }

        $this->set_product_categories($product_id, $product_data);
    }

    private function update_existing_product($product_id, $product_data)
    {
        $product = wc_get_product($product_id);
        //echo '<b>EXISTEIX</b>: ' . $product_data->Producto . ' ------ ' . $product_data->Precio_venta . ' €<br>';
        if ($product) {
            $product->set_name($product_data->Producto);
            $product->set_regular_price($product_data->Precio_venta);
            $product->set_stock_quantity($product_data->Existencias);
            $product->set_description($product_data->Descripcion);
            $product->set_tax_status('taxable');
            if (isset($product_data->IVA)) {
                if ($product_data->IVA == 10) {
                    $product->set_tax_class('IVA 10');
                } elseif ($product_data->IVA == 21) {
                    $product->set_tax_class('IVA 21');
                } else {
                    $product->set_tax_class('');
                }
            }

            $product->save();

            $this->set_product_categories($product_id, $product_data);
        }
    }
    function set_product_categories($product_id, $product_data)
    {
        $main_category_name = $product_data->Familia_agrupacion1;
        $sub_category_level_1_name = $product_data->Familia_agrupacion2;
        $sub_category_level_2_name = $product_data->Familia_agrupacion3;

        $category_ids = array();

        if ($main_category_name && $main_category_name !== '.') {
            $main_category = get_term_by('name', $main_category_name, 'product_cat');
            if (!$main_category) {
                $main_category = wp_insert_term($main_category_name, 'product_cat');
            }
            $category_ids[] = is_array($main_category) ? $main_category['term_id'] : $main_category->term_id;
        }

        if ($sub_category_level_1_name && $sub_category_level_1_name !== '.') {
            $sub_category_level_1 = get_term_by('name', $sub_category_level_1_name, 'product_cat');
            if (!$sub_category_level_1) {
                $sub_category_level_1 = wp_insert_term($sub_category_level_1_name, 'product_cat', array(
                    'parent' => $category_ids[0], // Categoría padre es la principal
                ));
            }
            $category_ids[] = is_array($sub_category_level_1) ? $sub_category_level_1['term_id'] : $sub_category_level_1->term_id;
        }

        if ($sub_category_level_2_name && $sub_category_level_2_name !== '.') {
            $sub_category_level_2 = get_term_by('name', $sub_category_level_2_name, 'product_cat');
            if (!$sub_category_level_2) {
                $sub_category_level_2 = wp_insert_term($sub_category_level_2_name, 'product_cat', array(
                    'parent' => end($category_ids), // Categoría padre es la subcategoría de nivel 1
                ));
            }
            $category_ids[] = is_array($sub_category_level_2) ? $sub_category_level_2['term_id'] : $sub_category_level_2->term_id;
        }

        wp_set_post_terms($product_id, $category_ids, 'product_cat');
    }

    public function check_API_product($codigo){
        
        $this->woosync_login_api();
        
        //$url = $this->options['woosync_api_url'] . "/select/Articulos?filter=(Codigo%20eq%$codigo)&fields=Existencias,Codigo";
        $url = $this->options['woosync_api_url'] . "/select/Articulos?filter=(Codigo%20eq%20$codigo)";        
        $response = wp_remote_get($url, [
            'headers' => [
                'Access-Token' => $this->access_token,
            ]
        ]);        

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data_set = array();        
        if ($response_code === 200) {
            $data = json_decode(json_decode($body, true));
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<div class="error"><p>Error decodificando JSON: ' . json_last_error_msg() . '</p></div>';    

            }
            if (isset($data->data) && is_array($data->data)) {
                $data_set = $data->data[0];
            } else {
                echo '<div class="error"><p>Error: No se encontraron datos de productos en la respuesta de la API.</p></div>';               
            }
        } else {
            echo '<div class="error"><p>Error en la sincronización: Código de respuesta ' . $response_code . '</p></div>';      
            return;              
        }
        $this->woosync_logout_api();
        
        return $data_set;
    }

    public function check_API_client($dni_client){

        $this->woosync_login_api();
        $url = $this->options['woosync_api_url'] . "/select/Clientes?filter=(Documento_identidad%20eq%20'$code_client')";        
        $response = wp_remote_get($url, [
            'headers' => [
                'Access-Token' => $this->access_token,
            ]
        ]);   
        $this->woosync_logout_api();

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data_set = array();        
        if ($response_code === 200) {
            $data = json_decode(json_decode($body, true));
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<div class="error"><p>Error decodificando JSON: ' . json_last_error_msg() . '</p></div>';    
                return false;
            }
            if (isset($data->data) && is_array($data->data)) {
                $data_set = $data->data[0];
                return true;
            } else {
                echo '<div class="error"><p>Error: No se ha encontrado un cliente con ese DNI</p></div>';    
                return false;           
            }
        } else {
            echo '<div class="error"><p>Error en la sincronización: Código de respuesta ' . $response_code . '</p></div>';      
            return false;              
        }                
    }


//Pendent de revisió, només està el codi de "presuntament" ha de funcionar

    public function set_API_client($data_client){

        $data_client_json = json_encode($data_client);

        $this->woosync_login_api();
        $url = $this->options['woosync_api_url'] . "/insert/Clientes?data=urlencode($data_client_json)";        
        $response = wp_remote_get($url, [
            'headers' => [
                'Access-Token' => $this->access_token,
            ]
        ]);   
        $this->woosync_logout_api();
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data_set = array();        
        if ($response_code === 200) {
            $data = json_decode(json_decode($body, true));
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<div class="error"><p>Error decodificando JSON: ' . json_last_error_msg() . '</p></div>';    
                return false;
            }
            if (isset($data->data) && is_array($data->data)) {
                $data_set = $data->data[0];
                return true;
            } else {
                echo '<div class="error"><p>Error: No se ha podido crear el cliente</p></div>';    
                return false;           
            }
        } else {
            echo '<div class="error"><p>Error en la sincronización: Código de respuesta ' . $response_code . '</p></div>';      
            return false;              
        }                
    }

    public function insert_API_factura($facuta, $client){

        $this->woosync_login_api();
        $url = $this->options['woosync_api_url'] . "/insert/Factura?data=urlencode($data_client_json)";        
        $response = wp_remote_get($url, [
            'headers' => [
                'Access-Token' => $this->access_token,
            ]
        ]);   
        $this->woosync_logout_api();

    }
}

// codigo nuevo
?>
