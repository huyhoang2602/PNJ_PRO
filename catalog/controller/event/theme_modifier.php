<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Event;

class ThemeModifier extends \Opencart\System\Engine\Controller {
    
    /**
     * @param string $route
     * @param array  $args   
     * @param string $output
     */
	public function injectHeaderData(string &$route, array &$args, mixed &$output): void {
        // Here we can inject data into common/header before it renders
        // For example, custom mega menu structure or additional links.
        /* 
        $args['dc_custom_menu'] = [
            ['name' => 'Collection', 'href' => $this->url->link('product/category', 'path=20')],
            ['name' => 'Brands', 'href' => $this->url->link('product/manufacturer')],
        ];
        */
	}

    /**
     * @param string $route
     * @param array  $args   
     * @param string $output
     */
    public function injectFooterData(string &$route, array &$args, mixed &$output): void {
        // Inject footer data
        $args['dc_hotline'] = $this->config->get('theme_dc_minimal_phone') ?? '1800 1234';
		$args['dc_email'] = $this->config->get('theme_dc_minimal_email') ?? 'cskh@domain.com';
    }

    /**
     * @param string $route
     * @param array  $args   
     * @param string $output
     */
    public function modifyProductList(string &$route, array &$args, mixed &$output): void {
        // This can be hooked into catalog/view/product/category/before
        // to modify $args['products'] before they are rendered by the category twig.
        if (isset($args['products']) && is_array($args['products'])) {
            foreach ($args['products'] as &$product) {
                // Determine discount percentage if special price exists
                if ($product['special']) {
                    $price_num = (float)preg_replace('/[^0-9.]/', '', $product['price']);
                    $special_num = (float)preg_replace('/[^0-9.]/', '', $product['special']);
                    
                    if ($price_num > 0) {
                        $discount = round((($price_num - $special_num) / $price_num) * 100);
                        $product['dc_discount_percent'] = '-' . $discount . '%';
                    }
                } else {
                    $product['dc_discount_percent'] = false;
                }
            }
        }
    }
}
