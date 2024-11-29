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


function registrer_cron() {
    if ( ! wp_next_scheduled( 'cron_job' ) ) {        
        wp_schedule_event( strtotime( '06:55:00' ), 'daily', 'cron_job' );        
        wp_schedule_event( strtotime( '13:55:00' ), 'daily', 'cron_job' );        
    }
}
register_activation_hook( __FILE__, 'registrer_cron' );

function write_log_info() {
    $filename = 'log.txt';
    $filepath = plugin_dir_path( __FILE__ );
    $file = fopen($filepath.$filename, 'a');
    fwrite( $file, "Fecha: " . date( 'Y-m-d H:i:s' ) . PHP_EOL );        
    fclose( $file );
}
add_action( 'cron_job', 'write_log_info' );

function delete_cron_job() {
    $timestamp = wp_next_scheduled( 'cron_job' );
    wp_unschedule_event( $timestamp, 'cron_job' );
}
register_deactivation_hook( __FILE__, 'delete_cron_job' );

add_action('woocommerce_check_cart_items', 'verificar_stock_cart', 10, 0);

function verificar_stock_cart() {    
    
    $plugin = new WooSyncPlugin();
    $plugin->woosync_login_api();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {        
        
        $product = $cart_item['data'];
        $sku_producto = $product->get_sku();
        $product_id = $product->get_id();
        $codigo = get_post_meta($product_id, '_Codigo', true);
        
        //check product in API
        $product_data = $plugin->check_API_product($sku_producto);
        //Update Product in DB
        $plugin->insert_product_into_db($product_data);
        //Update Product in WC
        $plugin->create_or_update_product($product_data);

        if ($product->get_stock_quantity() < $cart_item['quantity']) {        
            wc_add_notice(
                sprintf(
                    'Lo siento, pero no hay suficiente stock para el producto "%s". Quedan solo %d unidades.',
                    $product->get_name(),
                    $product->get_stock_quantity()
                ),
                'error'
            );
        }
    }
    $plugin->woosync_logout_api();
}

add_action('woocommerce_update_products', 'update_products', 10, 0);

function update_products(){
   
    $plugin = new WooSyncPlugin();
    $plugin->sync_products();
    $plugin->sync_wc_products();

}


?>
