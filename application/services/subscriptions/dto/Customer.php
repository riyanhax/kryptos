<?php

class Application_Service_Subscription_DTO_Customer
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $email;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /**
     * @param int $id
     * @param string $email
     * @param string $phone
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct($id, $email, $phone, $firstName, $lastName)
    {
        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
