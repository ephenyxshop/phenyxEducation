<?php

/**
 * Class AdminStatsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminStatsControllerCore extends AdminStatsTabController {

        
    /**
     * Get Orders
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param bool   $granularity
     *
     * @return array|false|null|string
     *
     * @since 1.9.1.0
     */
	public static function getTotalSales($dateFrom, $dateTo, $granularity = false) {

     	
		if ($granularity == 'day') {
            $sales = [];
			
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
             SELECT LEFT(`date_add`, 10) as date, SUM(price) as sales
            FROM `' . _DB_PREFIX_ . 'student_education` 
            WHERE  `id_student_education_state` > 3 AND `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            GROUP BY LEFT(`date_add`, 10)
           '
            );

            foreach ($result as $row) {
                $sales[$row['date']] = $row['sales'];
            }

            return $sales;
        } else if ($granularity == 'month') {
            $sales = [];
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
             SELECT LEFT(`date_add`, 7) as date, SUM(price) as sales
            FROM `' . _DB_PREFIX_ . 'student_education` 
            WHERE  `id_student_education_state` > 3 AND `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            GROUP BY LEFT(`date_add`, 7)
           '
            );

            foreach ($result as $row) {
                $sales[$row['date'] . '-01'] = $row['sales'];
            }

            return $sales;
        } else {
            return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                '
            SELECT SUM(price) as sales
            FROM `' . _DB_PREFIX_ . 'student_education`
            WHERE `id_student_education_state` > 3 AND `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            '
            );
        }

    }

	
	public static function getPrevisionnel($dateFrom, $dateTo, $granularity = false) {
		
		
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
             SELECT
            LEFT(se.`date_add`, 10) as date, SUM(se.price) as amount
            FROM `' . _DB_PREFIX_ . 'student_education` se
            WHERE se.`id_student_education_state` > 3 AND se.`id_student_education_state` < 8 AND  se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59" 
            GROUP BY LEFT(se.`date_add`, 10)'
            );
			
			
			
            foreach ($result as $row) {
				
				
                $previsionnel[$row['date']] = $row['amount'];
            }
			

            return $previsionnel;
		
						
			
	}
	
	 public static function getPurchases($dateFrom, $dateTo, $granularity = false) {

        
		$file = fopen("testgetPurchases.txt","w");
		fwrite($file,'
              SELECT LEFT(se.`date_add`, 10) as date, SUM(se.price) as sales, COUNT(id_student_education)*f.price  as cost
            FROM `' . _DB_PREFIX_ . 'student_education` se
			LEFT JOIN `' . _DB_PREFIX_ . 'formatpack` f ON(f.id_formatpack = se.id_formatpack)
            WHERE  se.`id_student_education_state` > 3 AND se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            GROUP BY LEFT(se.`date_add`, 10)
           ');
		
		if ($granularity == 'day') {
			
            $purchases = [];
			
			
			 $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
             SELECT LEFT(se.`date_add`, 10) as date, SUM(se.price) as sales, COUNT(id_student_education)*f.price  as cost
            FROM `' . _DB_PREFIX_ . 'student_education` se
			LEFT JOIN `' . _DB_PREFIX_ . 'formatpack` f ON(f.id_formatpack = se.id_formatpack)
            WHERE  se.`id_student_education_state` > 3 AND se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            GROUP BY LEFT(se.`date_add`, 10)
           '
            );

            foreach ($result as $row) {
                $purchases[$row['date']] = $row['sales'] - $row['cost'];
            }
			
           

            return $purchases;
        } else {
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                '
             SELECT SUM(total_tax_incl) as sales, SUM(piece_margin) as margin
            FROM `' . _DB_PREFIX_ . 'customer_pieces` 
            WHERE  `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"'
            );
			
			foreach ($result as $row) {
				$purchases[$row['date']] = $row['sales'] - $row['margin'];
            }
			return $purchases;
        }

    }

	
	
	
  
	
	public static function getVdiSales($dateFrom, $dateTo, $granularity = false) {

        if ($granularity == 'day') {
            $orders = [];
						
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
            SELECT
            LEFT(se.`date_add`, 10) as date, SUM(se.price) as sales
            FROM `' . _DB_PREFIX_ . 'student_education` se
            WHERE se.`id_student_education_state` > 3 AND se.`id_sale_agent` > 0 AND se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59" 
            GROUP BY LEFT(se.`date_add`, 10)'
            );
			
			
            foreach ($result as $row) {
			     $orders[$row['date']] = $row['sales'];
            }
			

            return $orders;
        } else if ($granularity == 'month') {
            $orders = [];
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
                '
            SELECT
            LEFT(se.`date_add`, 7) as date, SUM(se.price) as commission
            FROM `' . _DB_PREFIX_ . 'student_education` se
            WHERE se.`id_student_education_state` > 3 AND se.`id_sale_agent` > 0 AND se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59" 
            GROUP BY LEFT(se.`date_add`, 7)'
            );

            foreach ($result as $row) {
                $orders[strtotime($row['date'] . '-01')] = $row['commission'];
            }

            return $orders;
        } else {
            $orders = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                '
            SELECT COUNT(*) as nbSale
            FROM `' . _DB_PREFIX_ . 'student_education`
            WHERE `id_sale_agent` > 0  AND `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
            '
            );
        }

        return $orders;
      
    }
	
	public static function getAgentsPerformances($dateFrom, $dateTo, $granularity = false) {

		$search = '';
		$agents = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS((new DbQuery())
			->select('sa.*,  ( SELECT SUM(price) FROM `' . _DB_PREFIX_ . 'student_education` se WHERE se.id_sale_agent = sa.id_sale_agent AND se.id_student_education_state > 4 AND se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59") as total_turnover')
			->from('sale_agent', 'sa')
			->orderBy('`total_turnover` DESC')
			->limit('10'));
			
		foreach($agents as $agent) {
			$search = $search.$agent['id_sale_agent'].', ';
		}
		$search = substr($search, 0, -2);
    
		if ($granularity == 'day') {
            $orders = [];
									
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
              (new DbQuery())
				->select('LEFT(se.`date_add`, 10) as date, sa.firstname, sa.lastname, SUM(se.price) as total_turnover')
				->from('student_education', 'se')
				->leftJoin('sale_agent', 'sa', 'sa.id_sale_agent = se.id_sale_agent')
				->where('se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59" AND se.id_sale_agent IN ('.$search.')')
				->orderBy('`total_turnover` DESC')
				->groupBy('LEFT(se.`date_add`, 10)')
            );
			
            foreach ($result as $row) {
				
                $orders[strtotime($row['date'])] = $row;
            }
			

            return $orders;
        } else if ($granularity == 'month') {
            $orders = [];
			
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS(
              (new DbQuery())
				->select('LEFT(se.`date_add`, 7) as date, sa.firstname, sa.lastname, SUM(se.price) as total_turnover')
				->from('student_education', 'se')
				->leftJoin('sale_agent', 'sa', 'sa.id_sale_agent = se.id_sale_agent')
				->where('se.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59" AND se.id_sale_agent IN ('.$search.')')
				->orderBy('`total_turnover` DESC')
				->groupBy('LEFT(se.`date_add`, 7)')
            );
			
            
            foreach ($result as $row) {
                $orders[strtotime($row['date'] . '-01')] = $row;
            }

            return $orders;
        } else {
            $orders = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                '
            SELECT COUNT(*) as nbSale
            FROM `' . _DB_PREFIX_ . 'student_education`
            WHERE `id_sale_agent` > 0  AND `date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
			AND se.id_sale_agent IN ('.$search.')
            '
            );
        }

        return $orders;
      
    }

   

    /**
     * Get installed modules
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     */
    public static function getInstalledModules() {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(DISTINCT m.`id_module`)
        FROM `' . _DB_PREFIX_ . 'module` m
        ' . Shop::addSqlAssociation('module', 'm')
        );
    }

    /**
     * Get disabled modules
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     */
    public static function getDisabledModules() {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'module` m
        ' . Shop::addSqlAssociation('module', 'm', false) . '
        WHERE module_shop.id_module IS NULL OR m.active = 0'
        );
    }

    /**
     * Get modules to update
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getModulesToUpdate() {

        $context = Context::getContext();
        $loggedOnAddons = false;

        if (isset($context->cookie->username_addons) && isset($context->cookie->password_addons)
            && !empty($context->cookie->username_addons) && !empty($context->cookie->password_addons)
        ) {
            $loggedOnAddons = true;
        }

        $modules = Module::getModulesOnDisk(true, $loggedOnAddons, $context->employee->id);
        $upgradeAvailable = 0;

        foreach ($modules as $km => $module) {

            if ($module->installed && isset($module->version_addons) && $module->version_addons) {
                // SimpleXMLElement
                ++$upgradeAvailable;
            }

        }

        return $upgradeAvailable;
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     */
    public static function getPercentProductStock() {

        $row = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            '
        SELECT SUM(IF(IFNULL(stock.quantity, 0) > 0, 1, 0)) as with_stock, COUNT(*) as products
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON p.id_product = pa.id_product
        ' . Product::sqlStock('p', 'pa') . '
        WHERE product_shop.active = 1'
        );

        return round($row['products'] ? 100 * $row['with_stock'] / $row['products'] : 0, 2) . '%';
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     */
    public static function getPercentProductOutOfStock() {

        $row = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            '
        SELECT SUM(IF(IFNULL(stock.quantity, 0) = 0, 1, 0)) as without_stock, COUNT(*) as products
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON p.id_product = pa.id_product
        ' . Product::sqlStock('p', 'pa') . '
        WHERE product_shop.active = 1'
        );

        return round($row['products'] ? 100 * $row['without_stock'] / $row['products'] : 0, 2) . '%';
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     */
    public static function getProductAverageGrossMargin() {

        $sql = 'SELECT AVG(1 - (IF(IFNULL(product_attribute_shop.wholesale_price, 0) = 0, product_shop.wholesale_price,product_attribute_shop.wholesale_price) / (IFNULL(product_attribute_shop.price, 0) + product_shop.price)))
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON p.id_product = pa.id_product
        ' . Shop::addSqlAssociation('product_attribute', 'pa', false) . '
        WHERE product_shop.active = 1';
        $value = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);

        return round(100 * $value, 2) . '%';
    }

    /**
     * Get disabled categories
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getDisabledCategories() {

        return (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'category` c
        ' . Shop::addSqlAssociation('category', 'c') . '
        WHERE c.active = 0'
        );
    }

    /**
     * Get disabled products
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getDisabledProducts() {

        return (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.active = 0'
        );
    }

    /**
     * Get total products
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getTotalProducts() {

        return (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p')
        );
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     *
     * @return string
     *
     * @since 1.9.1.0
     */
    public static function get8020SalesCatalog($dateFrom, $dateTo) {

        $distinctProducts = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(DISTINCT od.product_id)
        FROM `' . _DB_PREFIX_ . 'orders` o
        LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.id_order = od.id_order
        WHERE `invoice_date` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
        ' . Shop::addSqlRestriction(false, 'o')
        );

        if (!$distinctProducts) {
            return '0%';
        }

        return round(100 * $distinctProducts / AdminStatsController::getTotalProducts()) . '%';
    }

    /**
     * Get empty categories
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getEmptyCategories() {

        $total = (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'category` c
        ' . Shop::addSqlAssociation('category', 'c') . '
        AND c.active = 1
        AND c.nright = c.nleft + 1'
        );
        $used = (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(DISTINCT cp.id_category)
        FROM `' . _DB_PREFIX_ . 'category` c
        LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON c.id_category = cp.id_category
        ' . Shop::addSqlAssociation('category', 'c') . '
        AND c.active = 1
        AND c.nright = c.nleft + 1'
        );

        return intval($total - $used);
    }

    /**
     * Get customer main gender
     *
     * @return array|bool
     *
     * @since 1.9.1.0
     */
    public static function getCustomerMainGender() {

        $row = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            '
        SELECT SUM(IF(g.id_gender IS NOT NULL, 1, 0)) as total, SUM(IF(type = 0, 1, 0)) as male, SUM(IF(type = 1, 1, 0)) as female, SUM(IF(type = 2, 1, 0)) as neutral
        FROM `' . _DB_PREFIX_ . 'customer` c
        LEFT JOIN `' . _DB_PREFIX_ . 'gender` g ON c.id_gender = g.id_gender
        WHERE c.active = 1 ' . Shop::addSqlRestriction()
        );

        if (!$row['total']) {
            return false;
        } else if ($row['male'] > $row['female'] && $row['male'] >= $row['neutral']) {
            return ['type' => 'male', 'value' => round(100 * $row['male'] / $row['total'])];
        } else if ($row['female'] >= $row['male'] && $row['female'] >= $row['neutral']) {
            return ['type' => 'female', 'value' => round(100 * $row['female'] / $row['total'])];
        }

        return ['type' => 'neutral', 'value' => round(100 * $row['neutral'] / $row['total'])];
    }

    /**
     * Get average customer age
     *
     * @return float
     *
     * @since 1.9.1.0
     */
    public static function getAverageCustomerAge() {

        $value = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT AVG(DATEDIFF("' . date('Y-m-d') . ' 00:00:00", birthday))
        FROM `' . _DB_PREFIX_ . 'customer` c
        WHERE active = 1
        AND birthday IS NOT NULL AND birthday != "0000-00-00" ' . Shop::addSqlRestriction()
        );

        return round($value / 365);
    }

    /**
     * Get pending messages
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getPendingMessages() {

        return StudentThread::getTotalStudentThreads('status LIKE "%pending%" OR status = "open"');
    }

    /**
     * Get average message response time
     *
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return float|int
     *
     * @since 1.9.1.0
     */
    public static function getAverageMessageResponseTime($dateFrom, $dateTo) {

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            '
        SELECT MIN(cm1.date_add) as question, MIN(cm2.date_add) as reply
        FROM `' . _DB_PREFIX_ . 'customer_message` cm1
        INNER JOIN `' . _DB_PREFIX_ . 'customer_message` cm2 ON (cm1.id_customer_thread = cm2.id_customer_thread AND cm1.date_add < cm2.date_add)
        JOIN `' . _DB_PREFIX_ . 'customer_thread` ct ON (cm1.id_customer_thread = ct.id_customer_thread)
        WHERE cm1.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
        AND cm1.id_employee = 0 AND cm2.id_employee != 0
        ' . Shop::addSqlRestriction() . '
        GROUP BY cm1.id_customer_thread'
        );
        $totalQuestions = $totalReplies = $threads = 0;

        foreach ($result as $row) {
            ++$threads;
            $totalQuestions += strtotime($row['question']);
            $totalReplies += strtotime($row['reply']);
        }

        if (!$threads) {
            return 0;
        }

        return round(($totalReplies - $totalQuestions) / $threads / 3600, 1);
    }

    /**
     * Get messages per thread
     *
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return float|int
     *
     * @since 1.9.1.0
     */
    public static function getMessagesPerThread($dateFrom, $dateTo) {

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            '
        SELECT COUNT(*) as messages
        FROM `' . _DB_PREFIX_ . 'customer_thread` ct
        LEFT JOIN `' . _DB_PREFIX_ . 'customer_message` cm ON (ct.id_customer_thread = cm.id_customer_thread)
        WHERE ct.`date_add` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
        ' . Shop::addSqlRestriction() . '
        AND status = "closed"
        GROUP BY ct.id_customer_thread'
        );
        $threads = $messages = 0;

        foreach ($result as $row) {
            ++$threads;
            $messages += $row['messages'];
        }

        if (!$threads) {
            return 0;
        }

        return round($messages / $threads, 1);
    }

    /**
     * Get main country
     *
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return array|bool|null|object
     *
     * @since 1.9.1.0
     */
    public static function getMainCountry($dateFrom, $dateTo) {

        $totalOrders = AdminStatsController::getOrders($dateFrom, $dateTo);

        if (!$totalOrders) {
            return false;
        }

        $row = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            '
        SELECT a.id_country, COUNT(*) as orders
        FROM `' . _DB_PREFIX_ . 'orders` o
        LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON o.id_address_delivery = a.id_address
        WHERE `invoice_date` BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
        ' . Shop::addSqlRestriction()
        );
        $row['orders'] = round(100 * $row['orders'] / $totalOrders, 1);

        return $row;
    }

    /**
     * Get total sales
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param bool   $granularity
     *
     * @return array|false|null|string
     */
    
    /**
     * Get expenses
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param bool   $granularity
     *
     * @return array|int|string
     *
     * @since 1.9.1.0
     */
    

    /**
     * Get purchases
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param bool   $granularity
     *
     * @return array|false|null|string
     *
     * @since 1.9.1.0
     */
   
    /**
     * Get total categories
     *
     * @return int
     *
     * @since 1.9.1.0
     */
    public static function getTotalCategories() {

        return (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'category` c
        ' . Shop::addSqlAssociation('category', 'c')
        );
    }

    /**
     * Get best category
     *
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     */
    public static function getBestCategory($dateFrom, $dateTo) {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            '
        SELECT ca.`id_category`
        FROM `' . _DB_PREFIX_ . 'category` ca
        LEFT JOIN `' . _DB_PREFIX_ . 'category_product` capr ON ca.`id_category` = capr.`id_category`
        LEFT JOIN (
            SELECT pr.`id_product`, t.`totalPriceSold`
            FROM `' . _DB_PREFIX_ . 'product` pr
            LEFT JOIN (
                SELECT pr.`id_product`,
                    IFNULL(SUM(cp.`product_quantity`), 0) AS totalQuantitySold,
                    IFNULL(SUM(cp.`product_price` * cp.`product_quantity`), 0) / o.conversion_rate AS totalPriceSold
                FROM `' . _DB_PREFIX_ . 'product` pr
                LEFT OUTER JOIN `' . _DB_PREFIX_ . 'order_detail` cp ON pr.`id_product` = cp.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = cp.`id_order`
                WHERE o.invoice_date BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
                GROUP BY pr.`id_product`
            ) t ON t.`id_product` = pr.`id_product`
        ) t ON t.`id_product` = capr.`id_product`
        WHERE ca.`level_depth` > 1
        GROUP BY ca.`id_category`
        ORDER BY SUM(t.`totalPriceSold`) DESC'
        );
    }

    public function setMedia() {

        parent::setMedia();
       
    }

}
