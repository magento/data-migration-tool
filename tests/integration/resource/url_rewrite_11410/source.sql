/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping structure for magento1.cms_page
DROP TABLE IF EXISTS `cms_page`;
CREATE TABLE IF NOT EXISTS `cms_page` (
  `page_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
  `title` varchar(255) DEFAULT NULL COMMENT 'Page Title',
  `root_template` varchar(255) DEFAULT NULL COMMENT 'Page Template',
  `meta_keywords` text COMMENT 'Page Meta Keywords',
  `meta_description` text COMMENT 'Page Meta Description',
  `identifier` varchar(100) DEFAULT NULL COMMENT 'Page String Identifier',
  `content_heading` varchar(255) DEFAULT NULL COMMENT 'Page Content Heading',
  `content` mediumtext COMMENT 'Page Content',
  `creation_time` timestamp NULL DEFAULT NULL COMMENT 'Page Creation Time',
  `update_time` timestamp NULL DEFAULT NULL COMMENT 'Page Modification Time',
  `is_active` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Is Page Active',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Page Sort Order',
  `layout_update_xml` text COMMENT 'Page Layout Update Content',
  `custom_theme` varchar(100) DEFAULT NULL COMMENT 'Page Custom Theme',
  `custom_root_template` varchar(255) DEFAULT NULL COMMENT 'Page Custom Template',
  `custom_layout_update_xml` text COMMENT 'Page Custom Layout Update Content',
  `custom_theme_from` date DEFAULT NULL COMMENT 'Page Custom Theme Active From Date',
  `custom_theme_to` date DEFAULT NULL COMMENT 'Page Custom Theme Active To Date',
  `published_revision_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Published Revision Id',
  `website_root` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'Website Root',
  `under_version_control` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Under Version Control Flag',
  PRIMARY KEY (`page_id`),
  KEY `IDX_CMS_PAGE_IDENTIFIER` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='CMS Page Table';

-- Dumping data for table magento1.cms_page: ~10 rows
-- DELETE FROM `cms_page`;
/*!40000 ALTER TABLE `cms_page` DISABLE KEYS */;
INSERT INTO `cms_page` (`page_id`, `title`, `root_template`, `meta_keywords`, `meta_description`, `identifier`, `content_heading`, `content`, `creation_time`, `update_time`, `is_active`, `sort_order`, `layout_update_xml`, `custom_theme`, `custom_root_template`, `custom_layout_update_xml`, `custom_theme_from`, `custom_theme_to`, `published_revision_id`, `website_root`, `under_version_control`) VALUES
	(1, '404 Not Found 1', 'two_columns_right', 'Page keywords', 'Page description', 'no-route', NULL, '\r\n    <div class="page-head-alt"><h3>We are sorry, but the page you are looking for cannot be found.</h3></div>\r\n    <div>\r\n        <ul class="disc">\r\n            <li>If you typed the URL directly, please make sure the spelling is correct.</li>\r\n            <li>If you clicked on a link to get here, we must have moved the content.\r\n            <br/>Please try our store search box above to search for an item.</li>\r\n            <li>If you are not sure how you got here,\r\n            <a href="#" onclick="history.go(-1);">go back</a> to the previous page\r\n            or return to our <a href="{{store url=""}}">store homepage</a>.</li>\r\n        </ul>\r\n    </div>\n\n<!-- \r\n<div class="page-title"><h1>Whoops, our bad...</h1></div>\r\n<dl>\r\n    <dt>The page you requested was not found, and we have a fine guess why.</dt>\r\n    <dd>\r\n        <ul class="disc">\r\n            <li>If you typed the URL directly, please make sure the spelling is correct.</li>\r\n            <li>If you clicked on a link to get here, the link is outdated.</li>\r\n        </ul>\r\n    </dd>\r\n</dl>\r\n<dl>\r\n    <dt>What can you do?</dt>\r\n    <dd>Have no fear, help is near! There are many ways you can get back on track with Magento Store.</dd>\r\n    <dd>\r\n        <ul class="disc">\r\n            <li><a href="#" onclick="history.go(-1); return false;">Go back</a> to the previous page.</li>\r\n            <li>Use the search bar at the top of the page to search for your products.</li>\r\n            <li>Follow these links to get you back on track!<br /><a href="{{store url=""}}">Store Home</a>\r\n            <span class="separator">|</span> <a href="{{store url="customer/account"}}">My Account</a></li>\r\n        </ul>\r\n    </dd>\r\n</dl>\r\n -->\r\n', '2015-10-09 15:15:23', '2015-10-09 15:15:23', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(2, 'Home page', 'two_columns_right', NULL, NULL, 'home', NULL, '<div class="page-title"><h2>Home Page</h2></div>', '2015-10-09 15:15:24', '2015-10-09 15:15:42', 1, 0, '<!--<reference name="content">\r\n        <block type="catalog/product_new" name="home.catalog.product.new" alias="product_new" template="catalog/product/new.phtml" after="cms_page">\r\n            <action method="addPriceBlockType">\r\n                <type>bundle</type>\r\n                <block>bundle/catalog_product_price</block>\r\n                <template>bundle/catalog/product/price.phtml</template>\r\n            </action>\r\n        </block>\r\n        <block type="reports/product_viewed" name="home.reports.product.viewed" alias="product_viewed" template="reports/home_product_viewed.phtml" after="product_new">\r\n            <action method="addPriceBlockType">\r\n                <type>bundle</type>\r\n                <block>bundle/catalog_product_price</block>\r\n                <template>bundle/catalog/product/price.phtml</template>\r\n            </action>\r\n        </block>\r\n        <block type="reports/product_compared" name="home.reports.product.compared" template="reports/home_product_compared.phtml" after="product_viewed">\r\n            <action method="addPriceBlockType">\r\n                <type>bundle</type>\r\n                <block>bundle/catalog_product_price</block>\r\n                <template>bundle/catalog/product/price.phtml</template>\r\n            </action>\r\n        </block>\r\n    </reference>\r\n    <reference name="right">\r\n        <action method="unsetChild"><alias>right.reports.product.viewed</alias></action>\r\n        <action method="unsetChild"><alias>right.reports.product.compared</alias></action>\r\n    </reference>-->', NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(3, 'About Us', 'two_columns_right', NULL, NULL, 'about-magento-demo-store', NULL, '\r\n<div class="page-title">\r\n    <h1>About Magento Store</h1>\r\n</div>\r\n<div class="col3-set">\r\n<div class="col-1"><p style="line-height:1.2em;"><small>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.\r\nMorbi luctus. Duis lobortis. Nulla nec velit. Mauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec,\r\ntempus vitae, iaculis semper, pede.</small></p>\r\n<p style="color:#888; font:1.2em/1.4em georgia, serif;">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.\r\nMorbi luctus. Duis lobortis. Nulla nec velit. Mauris pulvinar erat non massa. Suspendisse tortor turpis,\r\nporta nec, tempus vitae, iaculis semper, pede. Cras vel libero id lectus rhoncus porta.</p></div>\r\n<div class="col-2">\r\n<p><strong style="color:#de036f;">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus.\r\nDuis lobortis. Nulla nec velit.</strong></p>\r\n<p>Vivamus tortor nisl, lobortis in, faucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper.\r\nPhasellus id massa. Pellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada\r\nfames ac turpis egestas. Nunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac,\r\ntempus nec, tempor nec, justo. </p>\r\n<p>Maecenas ullamcorper, odio vel tempus egestas, dui orci faucibus orci, sit amet aliquet lectus dolor et quam.\r\nPellentesque consequat luctus purus. Nunc et risus. Etiam a nibh. Phasellus dignissim metus eget nisi.\r\nVestibulum sapien dolor, aliquet nec, porta ac, malesuada a, libero. Praesent feugiat purus eget est.\r\nNulla facilisi. Vestibulum tincidunt sapien eu velit. Mauris purus. Maecenas eget mauris eu orci accumsan feugiat.\r\nPellentesque eget velit. Nunc tincidunt.</p></div>\r\n<div class="col-3">\r\n<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper </p>\r\n<p><strong style="color:#de036f;">Maecenas ullamcorper, odio vel tempus egestas, dui orci faucibus orci,\r\nsit amet aliquet lectus dolor et quam. Pellentesque consequat luctus purus.</strong></p>\r\n<p>Nunc et risus. Etiam a nibh. Phasellus dignissim metus eget nisi.</p>\r\n<div class="divider"></div>\r\n<p>To all of you, from all of us at Magento Store - Thank you and Happy eCommerce!</p>\r\n<p style="line-height:1.2em;"><strong style="font:italic 2em Georgia, serif;">John Doe</strong><br />\r\n<small>Some important guy</small></p></div>\r\n</div>', '2015-10-09 15:15:24', '2015-10-09 15:15:24', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(4, 'Customer Service', 'three_columns', NULL, NULL, 'customer-service', NULL, '<div class="page-title">\r\n<h1>Customer Service</h1>\r\n</div>\r\n<ul class="disc">\r\n<li><a href="#answer1">Shipping &amp; Delivery</a></li>\r\n<li><a href="#answer2">Privacy &amp; Security</a></li>\r\n<li><a href="#answer3">Returns &amp; Replacements</a></li>\r\n<li><a href="#answer4">Ordering</a></li>\r\n<li><a href="#answer5">Payment, Pricing &amp; Promotions</a></li>\r\n<li><a href="#answer6">Viewing Orders</a></li>\r\n<li><a href="#answer7">Updating Account Information</a></li>\r\n</ul>\r\n<dl>\r\n<dt id="answer1">Shipping &amp; Delivery</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\nPellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\nNunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\njusto.</dd>\r\n<dt id="answer2">Privacy &amp; Security</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\nPellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\nNunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\njusto.</dd>\r\n<dt id="answer3">Returns &amp; Replacements</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\nPellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\nNunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\njusto.</dd>\r\n<dt id="answer4">Ordering</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\nPellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\nNunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\njusto.</dd>\r\n<dt id="answer5">Payment, Pricing &amp; Promotions</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\nPellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\nNunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\njusto.</dd>\r\n<dt id="answer6">Viewing Orders</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\nMauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\nCras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\nfaucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\n Pellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\n Nunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\n justo.</dd>\r\n<dt id="answer7">Updating Account Information</dt>\r\n<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi luctus. Duis lobortis. Nulla nec velit.\r\n Mauris pulvinar erat non massa. Suspendisse tortor turpis, porta nec, tempus vitae, iaculis semper, pede.\r\n Cras vel libero id lectus rhoncus porta. Suspendisse convallis felis ac enim. Vivamus tortor nisl, lobortis in,\r\n faucibus et, tempus at, dui. Nunc risus. Proin scelerisque augue. Nam ullamcorper. Phasellus id massa.\r\n Pellentesque nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\r\n Nunc augue. Aenean sed justo non leo vehicula laoreet. Praesent ipsum libero, auctor ac, tempus nec, tempor nec,\r\n justo.</dd>\r\n</dl>', '2015-10-09 15:15:24', '2015-10-09 15:15:24', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(5, 'Enable Cookies', 'one_column', NULL, NULL, 'enable-cookies', NULL, '<div class="std">\r\n    <ul class="messages">\r\n        <li class="notice-msg">\r\n            <ul>\r\n                <li>Please enable cookies in your web browser to continue.</li>\r\n            </ul>\r\n        </li>\r\n    </ul>\r\n    <div class="page-title">\r\n        <h1><a name="top"></a>What are Cookies?</h1>\r\n    </div>\r\n    <p>Cookies are short pieces of data that are sent to your computer when you visit a website.\r\n    On later visits, this data is then returned to that website. Cookies allow us to recognize you automatically\r\n    whenever you visit our site so that we can personalize your experience and provide you with better service.\r\n    We also use cookies (and similar browser data, such as Flash cookies) for fraud prevention and other purposes.\r\n     If your web browser is set to refuse cookies from our website, you will not be able to complete a purchase\r\n     or take advantage of certain features of our website, such as storing items in your Shopping Cart or\r\n     receiving personalized recommendations. As a result, we strongly encourage you to configure your web\r\n     browser to accept cookies from our website.</p>\r\n    <h2 class="subtitle">Enabling Cookies</h2>\r\n    <ul class="disc">\r\n        <li><a href="#ie7">Internet Explorer 7.x</a></li>\r\n        <li><a href="#ie6">Internet Explorer 6.x</a></li>\r\n        <li><a href="#firefox">Mozilla/Firefox</a></li>\r\n        <li><a href="#opera">Opera 7.x</a></li>\r\n    </ul>\r\n    <h3><a name="ie7"></a>Internet Explorer 7.x</h3>\r\n    <ol>\r\n        <li>\r\n            <p>Start Internet Explorer</p>\r\n        </li>\r\n        <li>\r\n            <p>Under the <strong>Tools</strong> menu, click <strong>Internet Options</strong></p>\r\n            <p><img src="{{skin url="images/cookies/ie7-1.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Click the <strong>Privacy</strong> tab</p>\r\n            <p><img src="{{skin url="images/cookies/ie7-2.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Click the <strong>Advanced</strong> button</p>\r\n            <p><img src="{{skin url="images/cookies/ie7-3.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Put a check mark in the box for <strong>Override Automatic Cookie Handling</strong>,\r\n            put another check mark in the <strong>Always accept session cookies </strong>box</p>\r\n            <p><img src="{{skin url="images/cookies/ie7-4.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Click <strong>OK</strong></p>\r\n            <p><img src="{{skin url="images/cookies/ie7-5.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Click <strong>OK</strong></p>\r\n            <p><img src="{{skin url="images/cookies/ie7-6.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Restart Internet Explore</p>\r\n        </li>\r\n    </ol>\r\n    <p class="a-top"><a href="#top">Back to Top</a></p>\r\n    <h3><a name="ie6"></a>Internet Explorer 6.x</h3>\r\n    <ol>\r\n        <li>\r\n            <p>Select <strong>Internet Options</strong> from the Tools menu</p>\r\n            <p><img src="{{skin url="images/cookies/ie6-1.gif"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Click on the <strong>Privacy</strong> tab</p>\r\n        </li>\r\n        <li>\r\n            <p>Click the <strong>Default</strong> button (or manually slide the bar down to <strong>Medium</strong>)\r\n            under <strong>Settings</strong>. Click <strong>OK</strong></p>\r\n            <p><img src="{{skin url="images/cookies/ie6-2.gif"}}" alt="" /></p>\r\n        </li>\r\n    </ol>\r\n    <p class="a-top"><a href="#top">Back to Top</a></p>\r\n    <h3><a name="firefox"></a>Mozilla/Firefox</h3>\r\n    <ol>\r\n        <li>\r\n            <p>Click on the <strong>Tools</strong>-menu in Mozilla</p>\r\n        </li>\r\n        <li>\r\n            <p>Click on the <strong>Options...</strong> item in the menu - a new window open</p>\r\n        </li>\r\n        <li>\r\n            <p>Click on the <strong>Privacy</strong> selection in the left part of the window. (See image below)</p>\r\n            <p><img src="{{skin url="images/cookies/firefox.png"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>Expand the <strong>Cookies</strong> section</p>\r\n        </li>\r\n        <li>\r\n            <p>Check the <strong>Enable cookies</strong> and <strong>Accept cookies normally</strong> checkboxes</p>\r\n        </li>\r\n        <li>\r\n            <p>Save changes by clicking <strong>Ok</strong>.</p>\r\n        </li>\r\n    </ol>\r\n    <p class="a-top"><a href="#top">Back to Top</a></p>\r\n    <h3><a name="opera"></a>Opera 7.x</h3>\r\n    <ol>\r\n        <li>\r\n            <p>Click on the <strong>Tools</strong> menu in Opera</p>\r\n        </li>\r\n        <li>\r\n            <p>Click on the <strong>Preferences...</strong> item in the menu - a new window open</p>\r\n        </li>\r\n        <li>\r\n            <p>Click on the <strong>Privacy</strong> selection near the bottom left of the window. (See image below)</p>\r\n            <p><img src="{{skin url="images/cookies/opera.png"}}" alt="" /></p>\r\n        </li>\r\n        <li>\r\n            <p>The <strong>Enable cookies</strong> checkbox must be checked, and <strong>Accept all cookies</strong>\r\n            should be selected in the &quot;<strong>Normal cookies</strong>&quot; drop-down</p>\r\n        </li>\r\n        <li>\r\n            <p>Save changes by clicking <strong>Ok</strong></p>\r\n        </li>\r\n    </ol>\r\n    <p class="a-top"><a href="#top">Back to Top</a></p>\r\n</div>\r\n', '2015-10-09 15:15:24', '2015-10-09 15:15:24', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(6, 'Privacy Policy', 'one_column', NULL, NULL, 'privacy-policy-cookie-restriction-mode', 'Privacy Policy', '<p style="color: #ff0000; font-weight: bold; font-size: 13px">\r\n    Please replace this text with you Privacy Policy.\r\n    Please add any additional cookies your website uses below (e.g., Google Analytics)\r\n</p>\r\n<p>\r\n    This privacy policy sets out how {{config path="general/store_information/name"}} uses and protects any information\r\n    that you give {{config path="general/store_information/name"}} when you use this website.\r\n    {{config path="general/store_information/name"}} is committed to ensuring that your privacy is protected.\r\n    Should we ask you to provide certain information by which you can be identified when using this website,\r\n    then you can be assured that it will only be used in accordance with this privacy statement.\r\n    {{config path="general/store_information/name"}} may change this policy from time to time by updating this page.\r\n    You should check this page from time to time to ensure that you are happy with any changes.\r\n</p>\r\n<h2>What we collect</h2>\r\n<p>We may collect the following information:</p>\r\n<ul>\r\n    <li>name</li>\r\n    <li>contact information including email address</li>\r\n    <li>demographic information such as postcode, preferences and interests</li>\r\n    <li>other information relevant to customer surveys and/or offers</li>\r\n</ul>\r\n<p>\r\n    For the exhaustive list of cookies we collect see the <a href="#list">List of cookies we collect</a> section.\r\n</p>\r\n<h2>What we do with the information we gather</h2>\r\n<p>\r\n    We require this information to understand your needs and provide you with a better service,\r\n    and in particular for the following reasons:\r\n</p>\r\n<ul>\r\n    <li>Internal record keeping.</li>\r\n    <li>We may use the information to improve our products and services.</li>\r\n    <li>\r\n        We may periodically send promotional emails about new products, special offers or other information which we\r\n        think you may find interesting using the email address which you have provided.\r\n    </li>\r\n    <li>\r\n        From time to time, we may also use your information to contact you for market research purposes.\r\n        We may contact you by email, phone, fax or mail. We may use the information to customise the website\r\n        according to your interests.\r\n    </li>\r\n</ul>\r\n<h2>Security</h2>\r\n<p>\r\n    We are committed to ensuring that your information is secure. In order to prevent unauthorised access or disclosure,\r\n    we have put in place suitable physical, electronic and managerial procedures to safeguard and secure\r\n    the information we collect online.\r\n</p>\r\n<h2>How we use cookies</h2>\r\n<p>\r\n    A cookie is a small file which asks permission to be placed on your computer\'s hard drive.\r\n    Once you agree, the file is added and the cookie helps analyse web traffic or lets you know when you visit\r\n    a particular site. Cookies allow web applications to respond to you as an individual. The web application\r\n    can tailor its operations to your needs, likes and dislikes by gathering and remembering information about\r\n    your preferences.\r\n</p>\r\n<p>\r\n    We use traffic log cookies to identify which pages are being used. This helps us analyse data about web page traffic\r\n    and improve our website in order to tailor it to customer needs. We only use this information for statistical\r\n    analysis purposes and then the data is removed from the system.\r\n</p>\r\n<p>\r\n    Overall, cookies help us provide you with a better website, by enabling us to monitor which pages you find useful\r\n    and which you do not. A cookie in no way gives us access to your computer or any information about you,\r\n    other than the data you choose to share with us. You can choose to accept or decline cookies.\r\n    Most web browsers automatically accept cookies, but you can usually modify your browser setting\r\n    to decline cookies if you prefer. This may prevent you from taking full advantage of the website.\r\n</p>\r\n<h2>Links to other websites</h2>\r\n<p>\r\n    Our website may contain links to other websites of interest. However, once you have used these links\r\n    to leave our site, you should note that we do not have any control over that other website.\r\n    Therefore, we cannot be responsible for the protection and privacy of any information which you provide whilst\r\n    visiting such sites and such sites are not governed by this privacy statement.\r\n    You should exercise caution and look at the privacy statement applicable to the website in question.\r\n</p>\r\n<h2>Controlling your personal information</h2>\r\n<p>You may choose to restrict the collection or use of your personal information in the following ways:</p>\r\n<ul>\r\n    <li>\r\n        whenever you are asked to fill in a form on the website, look for the box that you can click to indicate\r\n        that you do not want the information to be used by anybody for direct marketing purposes\r\n    </li>\r\n    <li>\r\n        if you have previously agreed to us using your personal information for direct marketing purposes,\r\n        you may change your mind at any time by writing to or emailing us at\r\n        {{config path="trans_email/ident_general/email"}}\r\n    </li>\r\n</ul>\r\n<p>\r\n    We will not sell, distribute or lease your personal information to third parties unless we have your permission\r\n    or are required by law to do so. We may use your personal information to send you promotional information\r\n    about third parties which we think you may find interesting if you tell us that you wish this to happen.\r\n</p>\r\n<p>\r\n    You may request details of personal information which we hold about you under the Data Protection Act 1998.\r\n    A small fee will be payable. If you would like a copy of the information held on you please write to\r\n    {{config path="general/store_information/address"}}.\r\n</p>\r\n<p>\r\n    If you believe that any information we are holding on you is incorrect or incomplete,\r\n    please write to or email us as soon as possible, at the above address.\r\n    We will promptly correct any information found to be incorrect.\r\n</p>\r\n<h2><a name="list"></a>List of cookies we collect</h2>\r\n<p>The table below lists the cookies we collect and what information they store.</p>\r\n<table class="data-table">\r\n    <thead>\r\n        <tr>\r\n            <th>COOKIE name</th>\r\n            <th>COOKIE Description</th>\r\n        </tr>\r\n    </thead>\r\n    <tbody>\r\n        <tr>\r\n            <th>CART</th>\r\n            <td>The association with your shopping cart.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CATEGORY_INFO</th>\r\n            <td>Stores the category info on the page, that allows to display pages more quickly.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>COMPARE</th>\r\n            <td>The items that you have in the Compare Products list.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CURRENCY</th>\r\n            <td>Your preferred currency</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CUSTOMER</th>\r\n            <td>An encrypted version of your customer id with the store.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CUSTOMER_AUTH</th>\r\n            <td>An indicator if you are currently logged into the store.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CUSTOMER_INFO</th>\r\n            <td>An encrypted version of the customer group you belong to.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>CUSTOMER_SEGMENT_IDS</th>\r\n            <td>Stores the Customer Segment ID</td>\r\n        </tr>\r\n        <tr>\r\n            <th>EXTERNAL_NO_CACHE</th>\r\n            <td>A flag, which indicates whether caching is disabled or not.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>FRONTEND</th>\r\n            <td>You sesssion ID on the server.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>GUEST-VIEW</th>\r\n            <td>Allows guests to edit their orders.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>LAST_CATEGORY</th>\r\n            <td>The last category you visited.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>LAST_PRODUCT</th>\r\n            <td>The most recent product you have viewed.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>NEWMESSAGE</th>\r\n            <td>Indicates whether a new message has been received.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>NO_CACHE</th>\r\n            <td>Indicates whether it is allowed to use cache.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>PERSISTENT_SHOPPING_CART</th>\r\n            <td>A link to information about your cart and viewing history if you have asked the site.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>POLL</th>\r\n            <td>The ID of any polls you have recently voted in.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>POLLN</th>\r\n            <td>Information on what polls you have voted on.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>RECENTLYCOMPARED</th>\r\n            <td>The items that you have recently compared.            </td>\r\n        </tr>\r\n        <tr>\r\n            <th>STF</th>\r\n            <td>Information on products you have emailed to friends.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>STORE</th>\r\n            <td>The store view or language you have selected.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>USER_ALLOWED_SAVE_COOKIE</th>\r\n            <td>Indicates whether a customer allowed to use cookies.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>VIEWED_PRODUCT_IDS</th>\r\n            <td>The products that you have recently viewed.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>WISHLIST</th>\r\n            <td>An encrypted list of products added to your Wishlist.</td>\r\n        </tr>\r\n        <tr>\r\n            <th>WISHLIST_CNT</th>\r\n            <td>The number of items in your Wishlist.</td>\r\n        </tr>\r\n    </tbody>\r\n</table>', '2015-10-09 15:15:25', '2015-10-09 15:15:25', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(7, '503 Service Unavailable', 'one_column', NULL, NULL, 'service-unavailable', NULL, '<div class="page-title"><h1>We\'re Offline...</h1></div>\r\n<p>...but only for just a bit. We\'re working to make the Magento Enterprise Demo a better place for you!</p>', '2015-10-09 15:15:43', '2015-10-09 15:15:43', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(8, 'Welcome to our Exclusive Online Store', 'one_column', NULL, NULL, 'private-sales', NULL, '<div class="private-sales-index">\r\n<div class="box">\r\n<div class="content">\r\n<h1>Welcome to our Exclusive Online Store</h1>\r\n<p>If you are a registered member, please <a href="{{store url="customer/account/login"}}">log in here</a>.</p>\r\n<p class="description">Magento is the leading hub for exclusive specialty items for all your home, apparel and entertainment needs!</p>\r\n</div>\r\n</div>\r\n</div>', '2015-10-09 15:15:43', '2015-10-09 15:15:43', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0),
	(9, 'Reward Points', 'one_column', NULL, NULL, 'reward-points', 'Reward Points', '<p>The Reward Points Program allows you to earn points for certain actions you take on the site. Points are awarded based on making purchases and customer actions such as submitting reviews.</p>\r\n\r\n<h2>Benefits of Reward Points for Registered Customers</h2>\r\n<p>Once you register you will be able to earn and accrue reward points, which are then redeemable at time of purchase towards the cost of your order. Rewards are an added bonus to your shopping experience on the site and just one of the ways we thank you for being a loyal customer.</p>\r\n\r\n<h2>Earning Reward Points</h2>\r\n<p>Rewards can currently be earned for the following actions:</p>\r\n<ul>\r\n<li>Making purchases — every time you make a purchase you earn points based on the price of products purchased and these points are added to your Reward Points balance.</li>\r\n<li>Registering on the site.</li>\r\n<li>Subscribing to a newsletter for the first time.</li>\r\n<li>Sending Invitations — Earn points by inviting your friends to join the site.</li>\r\n<li>Converting Invitations to Customer — Earn points for every invitation you send out which leads to your friends registering on the site.</li>\r\n<li>Converting Invitations to Order — Earn points for every invitation you send out which leads to a sale.</li>\r\n<li>Review Submission — Earn points for submitting product reviews.</li>\r\n<li>New Tag Submission — Earn points for adding tags to products.</li>\r\n</ul>\r\n\r\n<h2>Reward Points Exchange Rates</h2>\r\n<p>The value of reward points is determined by an exchange rate of both currency spent on products to points, and an exchange rate of points earned to currency for spending on future purchases.</p>\r\n\r\n<h2>Redeeming Reward Points</h2>\r\n<p>You can redeem your reward points at checkout. If you have accumulated enough points to redeem them you will have the option of using points as one of the payment methods.  The option to use reward points, as well as your balance and the monetary equivalent this balance, will be shown to you in the Payment Method area of the checkout.  Redeemable reward points can be used in conjunction with other payment methods such as credit cards, gift cards and more.</p>\r\n<p><img src="{{skin url="images/reward_points/payment.gif"}}" alt="Payment Information" /></p>\r\n\r\n<h2>Reward Points Minimums and Maximums</h2>\r\n<p>Reward points may be capped at a minimum value required for redemption.  If this option is selected you will not be able to use your reward points until you accrue a minimum number of points, at which point they will become available for redemption.</p>\r\n<p>Reward points may also be capped at the maximum value of points which can be accrued. If this option is selected you will need to redeem your accrued points before you are able to earn more points.</p>\r\n\r\n<h2>Managing My Reward Points</h2>\r\n<p>You have the ability to view and manage your points through your <a href="{{store url="customer/account"}}">Customer Account</a>. From your account you will be able to view your total points (and currency equivalent), minimum needed to redeem, whether you have reached the maximum points limit and a cumulative history of points acquired, redeemed and lost. The history record will retain and display historical rates and currency for informational purposes. The history will also show you comprehensive informational messages regarding points, including expiration notifications.</p>\r\n<p><img src="{{skin url="images/reward_points/my_account.gif"}}" alt="My Account" /></p>\r\n\r\n<h2>Reward Points Expiration</h2>\r\n<p>Reward points can be set to expire. Points will expire in the order form which they were first earned.</p>\r\n<p><strong>Note</strong>: You can sign up to receive email notifications each time your balance changes when you either earn, redeem or lose points, as well as point expiration notifications. This option is found in the <a href="{{store url="reward/customer/info"}}">Reward Points section</a> of the My Account area.</p>\r\n', '2015-10-09 15:15:44', '2015-10-09 15:15:44', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0);
/*!40000 ALTER TABLE `cms_page` ENABLE KEYS */;


-- Dumping structure for magento1.cms_page_store
DROP TABLE IF EXISTS `cms_page_store`;
CREATE TABLE IF NOT EXISTS `cms_page_store` (
  `page_id` smallint(6) NOT NULL COMMENT 'Page ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  PRIMARY KEY (`page_id`,`store_id`),
  KEY `IDX_CMS_PAGE_STORE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_CMS_PAGE_STORE_PAGE_ID_CMS_PAGE_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CMS_PAGE_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS Page To Store Linkage Table';

-- Dumping data for table magento1.cms_page_store: ~9 rows
DELETE FROM `cms_page_store`;
/*!40000 ALTER TABLE `cms_page_store` DISABLE KEYS */;
INSERT INTO `cms_page_store` (`page_id`, `store_id`) VALUES
	(1, 0),
	(2, 0),
	(3, 0),
	(4, 0),
	(5, 0),
	(6, 0),
	(7, 0),
	(8, 0),
	(9, 0);
/*!40000 ALTER TABLE `cms_page_store` ENABLE KEYS */;

--
-- Table structure for table `core_store`
--

DROP TABLE IF EXISTS `core_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_store` (
  `store_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Store Id',
  `code` varchar(32) DEFAULT NULL COMMENT 'Code',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Website Id',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Group Id',
  `name` varchar(255) NOT NULL COMMENT 'Store Name',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Sort Order',
  `is_active` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Activity',
  PRIMARY KEY (`store_id`),
  UNIQUE KEY `UNQ_CORE_STORE_CODE` (`code`),
  KEY `IDX_CORE_STORE_WEBSITE_ID` (`website_id`),
  KEY `IDX_CORE_STORE_IS_ACTIVE_SORT_ORDER` (`is_active`,`sort_order`),
  KEY `IDX_CORE_STORE_GROUP_ID` (`group_id`),
  CONSTRAINT `FK_CORE_STORE_GROUP_ID_CORE_STORE_GROUP_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `core_store_group` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CORE_STORE_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Stores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_store`
--

LOCK TABLES `core_store` WRITE;
/*!40000 ALTER TABLE `core_store` DISABLE KEYS */;
INSERT INTO `core_store` VALUES
(0,'admin',0,0,'Admin',0,1),
(1,'default',1,1,'Default Store View',0,1),
(2,'de',1,1,'German',0,1),
(3,'mw_store_02',1,2,'MWStore View02',0,1);
/*!40000 ALTER TABLE `core_store` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `core_config_data`
--

DROP TABLE IF EXISTS `core_config_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_config_data` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Config Id',
  `scope` varchar(8) NOT NULL DEFAULT 'default' COMMENT 'Config Scope',
  `scope_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Config Scope Id',
  `path` varchar(255) NOT NULL DEFAULT 'general' COMMENT 'Config Path',
  `value` text COMMENT 'Config Value',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `UNQ_CORE_CONFIG_DATA_SCOPE_SCOPE_ID_PATH` (`scope`,`scope_id`,`path`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 COMMENT='Config Data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_config_data`
--

LOCK TABLES `core_config_data` WRITE;
/*!40000 ALTER TABLE `core_config_data` DISABLE KEYS */;
INSERT INTO `core_config_data` VALUES
(1,'default',0,'catalog/seo/product_url_suffix','.html'),
(2,'default',0,'catalog/seo/category_url_suffix','.html'),
(3,'websites',1,'catalog/seo/product_url_suffix','.html1'),
(4,'stores',3,'catalog/seo/product_url_suffix','.html2');
/*!40000 ALTER TABLE `core_config_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise_url_rewrite`
--

DROP TABLE IF EXISTS `enterprise_url_rewrite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise_url_rewrite` (
  `url_rewrite_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Url Rewrite Id',
  `request_path` varchar(255) NOT NULL COMMENT 'Request Path',
  `target_path` varchar(255) NOT NULL COMMENT 'Target path',
  `is_system` smallint(5) unsigned NOT NULL COMMENT 'Is url rewrite System',
  `guid` varchar(32) NOT NULL COMMENT 'GUID',
  `identifier` varchar(255) NOT NULL COMMENT 'Unique url identifier',
  `inc` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Url increment',
  `value_id` int(10) unsigned NOT NULL COMMENT 'Entity table identifier',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `entity_type` smallint(5) unsigned NOT NULL COMMENT 'Url Rewrite Entity Type',
  PRIMARY KEY (`url_rewrite_id`),
  UNIQUE KEY `UNQ_ENTERPRISE_URL_REWRITE_REQUEST_PATH_STORE_ID_ENTITY_TYPE` (`request_path`,`store_id`,`entity_type`),
  KEY `IDX_ENTERPRISE_URL_REWRITE_IDENTIFIER` (`identifier`),
  KEY `IDX_ENTERPRISE_URL_REWRITE_VALUE_ID_GUID` (`value_id`,`guid`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='URL Rewrite';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enterprise_url_rewrite`
--

LOCK TABLES `enterprise_url_rewrite` WRITE;
/*!40000 ALTER TABLE `enterprise_url_rewrite` DISABLE KEYS */;
INSERT INTO `enterprise_url_rewrite` VALUES
(1,'test','catalog/category/view/id/3',1,'aafdc55c2b13623895ba2f0d586f69e1','test',1,3,1,2),
(28,'test2','catalog/category/view/id/4',1,'e310bd9490c57030e520593d9eb6ce2e','test2',1,4,1,2),
(29,'test2','catalog/category/view/id/4',1,'ecb44f765c18a3af00f24128d8b6a3b8','test2',1,4,2,2),
(30,'test','catalog/category/view/id/3',1,'962b97814939018fb8477ca2e7e03c35','test',1,3,2,2),
(33,'test2/test3','catalog/category/view/id/5',1,'e0afb234ba3561cdbaba88938a562a4b','test2/test3',1,5,1,2),
(34,'test2/test3','catalog/category/view/id/5',1,'9b0dfaa6bee6bf08eabe6b185fdf2df8','test2/test3',1,5,2,2),
(35,'test','catalog/category/view/id/3',1,'c8cab577a8c630cd873427bb6b90f96d','test',1,3,3,2),
(36,'test2','catalog/category/view/id/4',1,'10eaef8757f416c135a609dbec41ea77','test2',1,4,3,2),
(37,'test2/test3','catalog/category/view/id/5',1,'ed3ccf135ec704e3df20648b5ae7b8b0','test2/test3',1,5,3,2),
(46,'test','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test',1,10,0,3),
(47,'test-product','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test-product',1,11,3,3),
(48,'test-store-first','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test-store-first',1,12,1,3),
(49,'test1.html','contacts',0,'4a0c0b790c2cb138ef699611f922339b','test1.html',1,1,1,1),
(50,'test2/test3/test_product.html','catalog/product/view/id/4/category/5',0,'4a0c0b790c2cb138ef699611f922339b','test2/test3/test_product.html',1,2,1,1),
(52,'test1','catalog/category/view/id/6',1,'a5d86e86af7f3b8b870bd5378d5084f2','test1',1,6,1,2),
(53,'test1','catalog/category/view/id/6',1,'69c325cf7b8a05180bda36bc511c1cce','test1',1,6,2,2),
(54,'test1','catalog/category/view/id/6',1,'f7011308e6f8f4e431bca3efdd1026f0','test1',1,6,3,2),
(55,'test1','catalog/product/view/id/5',1,'ada15e3563bf79b2d5c67d5cd5069e68','test1',1,13,0,3),
(56,'test4','catalog/category/view/id/7',1,'bd0ccc57528c1f3f086e6a82b83741a6','test4',1,7,1,2),
(57,'test4','catalog/category/view/id/7',1,'6eff0e8bfa846138f11cfb4a7bf5410d','test4',1,7,2,2),
(58,'test4','catalog/category/view/id/7',1,'d7f037bfe70bc4681d129bc7f5dcc76d','test4',1,7,3,2),
(59,'test5.html','contacts',0,'941ecaa436b59edf3c32e6f58c666697','test5.html',1,6,1,1),
(60,'test5','catalog/category/view/id/8',1,'74c8106e92344a3dcbf5732d6d8534fd','test5',1,8,1,2),
(61,'test5','catalog/category/view/id/8',1,'6d98585d20ddb1677a4e6458dabfe6be','test5',1,8,2,2),
(62,'test5','catalog/category/view/id/8',1,'8375e1e3b337311dde8a4b84e5e05c3f','test5',1,8,3,2);
/*!40000 ALTER TABLE `enterprise_url_rewrite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise_url_rewrite_redirect`
--

DROP TABLE IF EXISTS `enterprise_url_rewrite_redirect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise_url_rewrite_redirect` (
  `redirect_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Redirect Id',
  `identifier` varchar(255) NOT NULL COMMENT 'Url identifier',
  `target_path` varchar(255) NOT NULL COMMENT 'Target path',
  `options` varchar(255) DEFAULT NULL COMMENT 'Redirect options',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description',
  `category_id` int(10) unsigned DEFAULT NULL COMMENT 'Category Id',
  `product_id` int(10) unsigned DEFAULT NULL COMMENT 'Product Id',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  PRIMARY KEY (`redirect_id`),
  UNIQUE KEY `UNQ_ENTERPRISE_URL_REWRITE_REDIRECT_IDENTIFIER_STORE_ID` (`identifier`,`store_id`),
  KEY `FK_ENT_URL_REWRITE_REDIRECT_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` (`category_id`),
  KEY `FK_ENT_URL_REWRITE_REDIRECT_PRD_ID_CAT_PRD_ENTT_ENTT_ID` (`product_id`),
  CONSTRAINT `FK_ENT_URL_REWRITE_REDIRECT_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENT_URL_REWRITE_REDIRECT_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='Permanent redirect';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enterprise_url_rewrite_redirect`
--

LOCK TABLES `enterprise_url_rewrite_redirect` WRITE;
/*!40000 ALTER TABLE `enterprise_url_rewrite_redirect` DISABLE KEYS */;
INSERT INTO `enterprise_url_rewrite_redirect` VALUES
(1,'test1.html','catalog/category/view/id/6','RP',NULL,6,NULL,1),
(2,'test2/test3/test_product.html','catalog/product/view/id/4/category/5','RP',NULL,5,4,1),
(3,'test1.html','catalog/category/view/id/6',NULL,NULL,6,NULL,2),
(4,'test1.html','catalog/category/view/id/6',NULL,NULL,6,NULL,3),
(6,'test5.html','contacts','RP',NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `enterprise_url_rewrite_redirect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_entity_url_key`
--

DROP TABLE IF EXISTS `catalog_category_entity_url_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_entity_url_key` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Category Url Key',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CATALOG_CATEGORY_ENTITY_URL_KEY_ENTITY_ID_STORE_ID` (`entity_id`,`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_URL_KEY_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_URL_KEY_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_URL_KEY_ENTT_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='Catalog Category Url Key Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_entity_url_key`
--

LOCK TABLES `catalog_category_entity_url_key` WRITE;
/*!40000 ALTER TABLE `catalog_category_entity_url_key` DISABLE KEYS */;
INSERT INTO `catalog_category_entity_url_key` VALUES
(1,3,43,1,1,'root-catalog'),
(2,3,43,1,2,'default-category'),
(3,3,43,0,3,'test'),
(4,3,43,0,4,'test2'),
(5,3,43,0,5,'test3'),
(6,3,43,0,6,'test1'),
(7,3,43,0,7,'test4'),
(8,3,43,0,8,'test5');
/*!40000 ALTER TABLE `catalog_category_entity_url_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_entity_url_key`
--

DROP TABLE IF EXISTS `catalog_product_entity_url_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_entity_url_key` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Product Url Key',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_PRD_ENTT_URL_KEY_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  UNIQUE KEY `UNQ_CATALOG_PRODUCT_ENTITY_URL_KEY_VALUE` (`value`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_URL_KEY_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_URL_KEY_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_URL_KEY_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='Catalog Product Url Key Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_entity_url_key`
--

LOCK TABLES `catalog_product_entity_url_key` WRITE;
/*!40000 ALTER TABLE `catalog_product_entity_url_key` DISABLE KEYS */;
INSERT INTO `catalog_product_entity_url_key` VALUES
(10,4,97,0,4,'test'),
(11,4,97,3,4,'test-product'),
(12,4,97,1,4,'test-store-first'),
(13,4,97,0,5,'test1');
/*!40000 ALTER TABLE `catalog_product_entity_url_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_product`
--

DROP TABLE IF EXISTS `catalog_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_product` (
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `position` int(11) NOT NULL DEFAULT '0' COMMENT 'Position',
  PRIMARY KEY (`category_id`,`product_id`),
  KEY `IDX_CATALOG_CATEGORY_PRODUCT_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_CAT_CTGR_PRD_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_PRD_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product To Category Linkage Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_product`
--

LOCK TABLES `catalog_category_product` WRITE;
/*!40000 ALTER TABLE `catalog_category_product` DISABLE KEYS */;
INSERT INTO `catalog_category_product` VALUES
(3,4,1),
(3,5,1),
(4,4,1),
(5,4,0);
/*!40000 ALTER TABLE `catalog_category_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_website`
--

DROP TABLE IF EXISTS `catalog_product_website`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_website` (
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product ID',
  `website_id` smallint(5) unsigned NOT NULL COMMENT 'Website ID',
  PRIMARY KEY (`product_id`,`website_id`),
  KEY `IDX_CATALOG_PRODUCT_WEBSITE_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_WEBSITE_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_WS_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product To Website Linkage Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_website`
--

LOCK TABLES `catalog_product_website` WRITE;
/*!40000 ALTER TABLE `catalog_product_website` DISABLE KEYS */;
INSERT INTO `catalog_product_website` VALUES
(4,1),
(5,1);
/*!40000 ALTER TABLE `catalog_product_website` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_product_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_PRD_ENTT_VCHR_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COMMENT='Catalog Product Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_entity_varchar`
--

LOCK TABLES `catalog_product_entity_varchar` WRITE;
/*!40000 ALTER TABLE `catalog_product_entity_varchar` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalog_product_entity_varchar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_category_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_CTGR_ENTT_VCHR_ENTT_TYPE_ID_ENTT_ID_ATTR_ID_STORE_ID` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ENTT_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='Catalog Category Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_entity_varchar`
--

LOCK TABLES `catalog_category_entity_varchar` WRITE;
/*!40000 ALTER TABLE `catalog_category_entity_varchar` DISABLE KEYS */;
INSERT INTO `catalog_category_entity_varchar` VALUES
(1,3,41,0,1,'Root Catalog'),
(2,3,41,1,1,'Root Catalog'),
(3,3,41,0,2,'Default Category');
/*!40000 ALTER TABLE `catalog_category_entity_varchar` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-29 19:44:38
