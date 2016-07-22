CREATE TABLE `core_url_rewrite` (
	`url_rewrite_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Rewrite Id',
	`store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
	`category_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Category Id',
	`product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Product Id',
	`id_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Id Path',
	`request_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Request Path',
	`target_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Target Path',
	`is_system` SMALLINT(5) UNSIGNED NULL DEFAULT '1' COMMENT 'Defines is Rewrite System',
	`options` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Options',
	`description` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Deascription',
	PRIMARY KEY (`url_rewrite_id`),
	UNIQUE INDEX `UNQ_CORE_URL_REWRITE_REQUEST_PATH_STORE_ID` (`request_path`, `store_id`),
	UNIQUE INDEX `UNQ_CORE_URL_REWRITE_ID_PATH_IS_SYSTEM_STORE_ID` (`id_path`, `is_system`, `store_id`),
	INDEX `IDX_CORE_URL_REWRITE_TARGET_PATH_STORE_ID` (`target_path`, `store_id`),
	INDEX `IDX_CORE_URL_REWRITE_ID_PATH` (`id_path`),
	INDEX `IDX_CORE_URL_REWRITE_STORE_ID` (`store_id`),
	INDEX `FK_CORE_URL_REWRITE_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID` (`product_id`),
	INDEX `FK_CORE_URL_REWRITE_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` (`category_id`),
	CONSTRAINT `FK_CORE_URL_REWRITE_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CORE_URL_REWRITE_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CORE_URL_REWRITE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Url Rewrites'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (1, 1, 3, 0, 'category/3', 'newcat.html', 'catalog/category/view/id/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (2, 2, 3, 0, 'category/3', 'newcat.html', 'catalog/category/view/id/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (3, 1, 3, 1, 'product/1/3', NULL, 'catalog/product/view/id/1/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (4, 1, 0, 1, 'product/1', NULL, 'catalog/product/view/id/1', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (5, 2, 3, 1, 'product/1/3', NULL, 'catalog/product/view/id/1/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (6, 2, 0, 1, 'product/1', NULL, 'catalog/product/view/id/1', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (7, 1, 3, 2, 'product/2/3', 'newcat/virtual-product.html', 'catalog/product/view/id/2/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (8, 1, 0, 2, 'product/2', 'virtual-product.html', 'catalog/product/view/id/2', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (9, 2, 3, 2, 'product/2/3', 'newcat/virtual-product.html', 'catalog/product/view/id/2/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (10, 2, 0, 2, 'product/2', 'virtual-product.html', 'catalog/product/view/id/2', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (11, 1, 3, 3, 'product/3/3', 'newcat/gift-card.html', 'catalog/product/view/id/3/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (12, 1, 0, 3, 'product/3', 'gift-card.html', 'catalog/product/view/id/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (13, 2, 3, 3, 'product/3/3', 'newcat/gift-card.html', 'catalog/product/view/id/3/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (14, 2, 0, 3, 'product/3', 'gift-card.html', 'catalog/product/view/id/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (15, 1, 3, 4, 'product/4/3', 'newcat/downloadable-product.html', 'catalog/product/view/id/4/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (16, 1, 0, 4, 'product/4', 'downloadable-product.html', 'catalog/product/view/id/4', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (17, 2, 3, 4, 'product/4/3', 'newcat/downloadable-product.html', 'catalog/product/view/id/4/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (18, 2, 0, 4, 'product/4', 'downloadable-product.html', 'catalog/product/view/id/4', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (19, 1, 3, 5, 'product/5/3', 'newcat/grouped-product.html', 'catalog/product/view/id/5/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (20, 1, 0, 5, 'product/5', 'grouped-product.html', 'catalog/product/view/id/5', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (21, 2, 3, 5, 'product/5/3', 'newcat/grouped-product.html', 'catalog/product/view/id/5/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (22, 2, 0, 5, 'product/5', 'grouped-product.html', 'catalog/product/view/id/5', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (23, 1, 3, 6, 'product/6/3', 'newcat/bundle-product.html', 'catalog/product/view/id/6/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (24, 1, 0, 6, 'product/6', 'bundle-product.html', 'catalog/product/view/id/6', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (25, 2, 3, 6, 'product/6/3', 'newcat/bundle-product.html', 'catalog/product/view/id/6/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (26, 2, 0, 6, 'product/6', 'bundle-product.html', 'catalog/product/view/id/6', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (29, 1, 0, 8, 'product/8', 'simple-product-1.html', 'catalog/product/view/id/8', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (30, 2, 0, 8, 'product/8', 'simple-product-1.html', 'catalog/product/view/id/8', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (31, 1, 3, 9, 'product/9/3', 'newcat/simple-product-2.html', 'catalog/product/view/id/9/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (32, 1, 0, 9, 'product/9', 'simple-product-2.html', 'catalog/product/view/id/9', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (33, 2, 3, 9, 'product/9/3', 'newcat/simple-product-2.html', 'catalog/product/view/id/9/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (34, 2, 0, 9, 'product/9', 'simple-product-2.html', 'catalog/product/view/id/9', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (35, 1, 3, 10, 'product/10/3', 'newcat/configurableproduct.html', 'catalog/product/view/id/10/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (36, 1, 0, 10, 'product/10', 'configurableproduct.html', 'catalog/product/view/id/10', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (37, 2, 3, 10, 'product/10/3', 'newcat/configurableproduct.html', 'catalog/product/view/id/10/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (38, 2, 0, 10, 'product/10', 'configurableproduct.html', 'catalog/product/view/id/10', 1, '', NULL);
