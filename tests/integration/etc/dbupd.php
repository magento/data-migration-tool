<?php

$id = 221;
/**/
$data = ";
INSERT INTO `catalog_product_entity` (`entity_id`, `entity_type_id`, `attribute_set_id`, `type_id`, `sku`, `created_at`, `updated_at`, `has_options`, `required_options`) VALUES
";
$flush = 10000;
$first = true;
for($id = 222;$id < 1000000; $id++) {
    $data .= ($first?'':',') . "($id, 10, 61, 'simple', 'mer_build_$id', '2014-08-21 12:42:09', '2014-10-23 07:38:24', 1, 0)\n";
    $first = false;
    $flush = $flush -1;
    if ($flush<=0) {
        file_put_contents('db_dump_source.sql', $data, FILE_APPEND);
        $data = ";
INSERT INTO `catalog_product_entity` (`entity_id`, `entity_type_id`, `attribute_set_id`, `type_id`, `sku`, `created_at`, `updated_at`, `has_options`, `required_options`) VALUES
";
        $flush = 10000;
        $first = true;
    }
}

/**/
file_put_contents('db_dump_source.sql', ";

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
", FILE_APPEND);
