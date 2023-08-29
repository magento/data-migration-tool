<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;

/**
 * Class password hash resolver
 */
class PasswordHashResolver
{

    /**
     * @var CustomerResourceModel
     */
    protected $customerResourceModel;

    /**
     * @param CustomerResourceModel $customerResourceModel
     */
    public function __construct(
        CustomerResourceModel $customerResourceModel
    ) {
        $this->customerResourceModel = $customerResourceModel;
    }

    /**
     * Resolve password hash
     *
     * @param Customer $customer
     * @return mixed
     */
    public function resolve(Customer $customer)
    {
        $select = $this->customerResourceModel->getConnection()->select();
        $select->from(
                ['ce' => $this->customerResourceModel->getTable('customer_entity_varchar')],
                'ce.value'
            );
        $select->where('ce.attribute_id = ?', $customer->getAttribute('password_hash')->getId());
        $select->where('ce.entity_id = ?', $customer->getId());
        $passwordHash = $select->getAdapter()->fetchOne($select);
        $passwordHash = $passwordHash ?: $customer->getPasswordHash();
        return $passwordHash;
    }
}
