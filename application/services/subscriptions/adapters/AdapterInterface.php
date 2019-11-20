<?php

use Application_Service_Subscription_DTO_Subscription as Subscription;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;

interface Application_Service_Subscription_Adapter_AdapterInterface
{
    /**
     * @param Zend_Http_Client $httpClient
     * @param Zend_Config $config
     */
    public function __construct(
        Zend_Http_Client $httpClient,
        Zend_Config $config
    );

    /**
     * @param string $id
     * @return SubscriptionPlan
     * @throws ParsingErrorException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws BadRequestException
     */
    public function getPlan($id);

    /**
     * @return SubscriptionPlan[]
     * @throws ParsingErrorException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws BadRequestException
     */
    public function getPlansList();

    /**
     * @param SubscriptionPlan[] $subscriptionPlans
     * @throws ParsingErrorException
     * @throws ServerErrorException
     * @throws PermissionDeniedException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function synchronizePlansList(array $subscriptionPlans);

    /**
     * @param SubscriptionPlan $newPlan
     * @param SubscriptionPlan [$oldPlan]
     * @throws ParsingErrorException
     * @throws ServerErrorException
     * @throws PermissionDeniedException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function synchronizePlan(SubscriptionPlan $newPlan, SubscriptionPlan $oldPlan = null);

    /**
     * @param Subscription $subscription
     * @return int
     * @throws ParsingErrorException
     * @throws ServerErrorException
     * @throws PermissionDeniedException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function create(Subscription $subscription);
}
