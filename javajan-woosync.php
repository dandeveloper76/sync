<?php
/*
Plugin Name: Javajan WooSync
Plugin URI: https://javajan.com
Description: Un plugin para añadir nuevas funcionalidades a WooCommerce.
Version: 1.0
Author: Javajan
Author URI: https://javajan.com
License: GPLv2 or later
Text Domain: woosync
*/

// Evitar el acceso directo.
if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_product_options_general_product_data', 'add_custom_product_fields');
function add_custom_product_fields()
{
    echo '<div class="options_group">';

    woocommerce_wp_text_input(array(
        'id' => '_Referencia_producto',
        'label' => __('Referencia producto', 'woocommerce'),
        'placeholder' => 'Introduce la Referencia de producto',
        'desc_tip' => 'true',
        'description' => __('Código único del producto.', 'woocommerce')
    ));

    woocommerce_wp_text_input(array(
        'id' => '_Marca',
        'label' => __('Marca', 'woocommerce'),
        'placeholder' => 'Introduce la marca del producto',
        'desc_tip' => 'true',
        'description' => __('Marca del producto.', 'woocommerce')
    ));


    echo '</div>';
}


// Crear una nueva pestaña en el editor de producto
add_filter('woocommerce_product_data_tabs', 'add_custom_product_tab');
function add_custom_product_tab($tabs)
{
    $tabs['lentes'] = array(
        'label' => __('Lentes', 'woocommerce'),
        'target' => 'lentes',
        'class' => array('show_if_simple', 'show_if_variable'),
        'priority' => 21,
    );
    $tabs['lentillas'] = array(
        'label' => __('Lentillas', 'woocommerce'),
        'target' => 'lentillas',
        'class' => array('show_if_simple', 'show_if_variable'),
        'priority' => 21,
    );
    $tabs['gafas'] = array(
        'label' => __('Gafas', 'woocommerce'),
        'target' => 'gafas',
        'class' => array('show_if_simple', 'show_if_variable'),
        'priority' => 21,
    );
    return $tabs;
}

// Añadir los campos personalizados en la pestaña personalizada
add_action('woocommerce_product_data_panels', 'add_lentes_fields');
function add_lentes_fields()
{
    echo '<div id="lentes" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';

    $lentes = [
        '_Lentes_Geometria' => __('Geometría de Lentes', 'woocommerce'),
        '_Lentes_Diametro' => __('Diámetro de Lentes', 'woocommerce'),
        '_Lentes_Esfera' => __('Esfera de Lentes', 'woocommerce'),
        '_Lentes_Cilindro' => __('Cilindro de Lentes', 'woocommerce'),
        '_Lentes_Eje' => __('Eje de Lentes', 'woocommerce'),
        '_Lentes_Adicion' => __('Adición de Lentes', 'woocommerce')
    ];

    foreach ($lentes as $field_id => $label) {
        woocommerce_wp_text_input(array(
            'id' => $field_id,
            'label' => $label,
            'desc_tip' => 'true',
            'description' => sprintf(__('Introduce %s del producto.', 'woocommerce'), strtolower($label)),
        ));
    }

    echo '</div>';
    echo '</div>';
}
add_action('woocommerce_product_data_panels', 'add_lentillas_fields');
function add_lentillas_fields()
{
    echo '<div id="lentillas" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';

    $lentillas = [
        '_Lentillas_Esfera' => __('Esfera de Lentillas', 'woocommerce'),
        '_Lentillas_Cilindro' => __('Cilindro de Lentillas', 'woocommerce'),
        '_Lentillas_Eje' => __('Eje de Lentillas', 'woocommerce'),
        '_Lentillas_Adicion' => __('Adición de Lentillas', 'woocommerce'),
        '_Lentillas_Radio' => __('Radio de Lentillas', 'woocommerce'),
        '_Lentillas_Radio2' => __('Radio2 de Lentillas', 'woocommerce'),
        '_Lentillas_CurvaPeriferica' => __('Curva Periférica de Lentillas', 'woocommerce'),
        '_Lentillas_Diametro' => __('Diámetro de Lentillas', 'woocommerce')
    ];

    foreach ($lentillas as $field_id => $label) {
        woocommerce_wp_text_input(array(
            'id' => $field_id,
            'label' => $label,
            'desc_tip' => 'true',
            'description' => sprintf(__('Introduce %s del producto.', 'woocommerce'), strtolower($label)),
        ));
    }

    echo '</div>';
    echo '</div>';
}
add_action('woocommerce_product_data_panels', 'add_gafas_fields');
function add_gafas_fields()
{
    echo '<div id="gafas" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';

    $gafas = [
        '_Gafas_Color' => __('Color de Gafas', 'woocommerce'),
        '_Gafas_TipoLente' => __('Tipo de Lente de Gafas', 'woocommerce'),
        '_Gafas_Calibre' => __('Calibre de Gafas', 'woocommerce'),
        '_Gafas_Puente' => __('Puente de Gafas', 'woocommerce'),
        '_Gafas_Varilla' => __('Varilla de Gafas', 'woocommerce')
    ];

    foreach ($gafas as $field_id => $label) {
        woocommerce_wp_text_input(array(
            'id' => $field_id,
            'label' => $label,
            'desc_tip' => 'true',
            'description' => sprintf(__('Introduce %s del producto.', 'woocommerce'), strtolower($label)),
        ));
    }


    echo '</div>';
    echo '</div>';
}

require_once plugin_dir_path(__FILE__) . 'includes/class-woosync-plugin.php';

function run_woosync_plugin()
{
    $plugin = new WooSyncPlugin();
    $plugin->run();
}

function create_api_products_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'api_products';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL para crear la tabla
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Codigo VARCHAR(50) NOT NULL UNIQUE,
        Centro INT,
        Codigo_equivalente VARCHAR(50),
        CodigoArticulo_proveedor VARCHAR(50),
        Id_proveedor INT,
        NombreCorto_proveedor VARCHAR(100),
        NombreLargo_proveedor VARCHAR(255),
        Descripcion TEXT,
        Clase_producto CHAR(1),
        Familia_agrupacion1 VARCHAR(100),
        Familia_agrupacion2 VARCHAR(100),
        Familia_agrupacion3 VARCHAR(100),
        Referencia_producto VARCHAR(50),
        Referencia_producto2 VARCHAR(50),
        Producto VARCHAR(255),
        Marca VARCHAR(100),
        Proveedor VARCHAR(100), 
        Modelo VARCHAR(100),
        Color VARCHAR(50),
        Atributo VARCHAR(100),
        Lentes_Geometria VARCHAR(50),
        Lentes_Diametro VARCHAR(50),
        Lentes_Esfera VARCHAR(50),
        Lentes_Cilindro VARCHAR(50),
        Lentes_Eje VARCHAR(50),
        Lentes_Adicion VARCHAR(50),
        Lentillas_Esfera VARCHAR(50),
        Lentillas_Cilindro VARCHAR(50),
        Lentillas_Eje VARCHAR(50),
        Lentillas_Adicion VARCHAR(50),
        Lentillas_Radio VARCHAR(50),
        Lentillas_Radio2 VARCHAR(50),
        Lentillas_CurvaPeriferica VARCHAR(50),
        Lentillas_Diametro VARCHAR(50),
        Gafas_Color VARCHAR(50),
        Gafas_TipoLente VARCHAR(50),
        Gafas_Calibre INT,
        Gafas_Puente INT,
        Gafas_Varilla INT,
        IVA DECIMAL(5,2),
        Precio_compra DECIMAL(10,2),
        Precio_venta DECIMAL(10,2),
        Precio_venta2 DECIMAL(10,2),
        Existencias INT,
        ExistenciasTotales INT,
        FechaAlta DATETIME,
        FechaBaja DATETIME,
        UltimaActualizacion DATETIME,
        UltimaEntrada DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_api_products_table');

function custom_increase_http_request_timeout($timeout)
{
    return 100; // Ajusta el tiempo de espera en segundos
}
add_filter('http_request_timeout', 'custom_increase_http_request_timeout');

run_woosync_plugin();

// Codigo NUEVO  //
//
// --- Funcionalidad de sincronización de clientes --- //
add_action('admin_menu', 'woosync_admin_menu');
function woosync_admin_menu()
{
    add_menu_page(
        'Sincronizar Clientes',
        'WooSync',
        'manage_options',
        'woosync_clientes',
        'woosync_clientes_page',
        'dashicons-sync',
        30
    );
}

// Página de administración para sincronización de clientes
function woosync_clientes_page()
{
    ?>
    <div class="wrap">
        <h1>Sincronización de Clientes desde la API</h1>
        <?php
        if (isset($_POST['sync_clientes'])) {
            $clientes = woosync_obtener_clientes();
            if (!empty($clientes)) {
                echo '<div class="updated"><p>Clientes sincronizados correctamente.</p></div>';
            } else {
                echo '<div class="error"><p>No se encontraron clientes o hubo un error en la sincronización.</p></div>';
            }
        }
        ?>
        <form method="post" action="">
            <input type="submit" name="sync_clientes" class="button-primary" value="Sincronizar Clientes">
        </form>
        <?php
        $clientes = woosync_obtener_clientes();
        if (!empty($clientes)) {
            echo '<table>';
            echo '<thead><tr><th>Nombre</th><th>NIF</th></tr></thead><tbody>';
            foreach ($clientes as $cliente) {
                $nombre = $cliente['Nombre'] ?? 'N/A';
                $nif = $cliente['Documento_identidad'] ?? 'N/A';
                echo "<tr><td>" . esc_html($nombre) . "</td><td>" . esc_html($nif) . "</td></tr>";
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No se encontraron clientes sincronizados.</p>';
        }
        ?>
    </div>
    <?php
}

// Obtener clientes desde la API utilizando cURL
function woosync_obtener_clientes()
{
    $access_token = '993054F9-5074-4CEA-85FE-7271D15221D9'; // Token
    $base_url = 'http://shop.visualgesopt.com:8099/api/select/Clientes'; 
    $filter = ' (Fecha ge 5/6/2019) and (Fecha lt 6/6/2019)'; 

    // Construcción de la URL final
    $url = $base_url . '?filter=' . urlencode($filter);

    // Registro la URL generada
    error_log('URL generada: ' . $url);

    // Inicializar cURL
    $ch = curl_init();

    // Configuración de la solicitud cURL
    curl_setopt($ch, CURLOPT_URL, $url); // La URL de la API
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Obtener la respuesta como string
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token, // El token de acceso en el encabezado
        'Content-Type: application/json' // Establecer el tipo de contenido como JSON
    ]);
    
    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Verificar si hubo algún error con cURL
    if(curl_errno($ch)) {
        error_log('Error en la solicitud cURL: ' . curl_error($ch));
        curl_close($ch); // Cerrar cURL
        return [];
    }

    // Verificar el código de respuesta HTTP
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($status_code !== 200) {
        error_log('Código de respuesta HTTP: ' . $status_code);
        error_log('Respuesta: ' . $response);
        curl_close($ch); // Cerrar cURL
        return [];
    }

    // Cerrar cURL
    curl_close($ch);

    // Decodificar la respuesta JSON
    $clientes = json_decode($response, true);

    // Verificar errores en la decodificación JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error en el formato JSON: ' . json_last_error_msg());
        return [];
    }

    return $clientes ?? [];
}


?>