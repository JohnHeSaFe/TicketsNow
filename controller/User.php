<?php

class User {
    private $id_user;
    private $email;
    private $password;
    private $name;
    private $first_surname;
    private $second_surname;
    private $id_role;
    private $profile_photo;
    private $birth_date;
    private $gender;

    public function __construct($id_user, $email, $password, $name, $first_surname, $second_surname, $birth_date, $gender, $id_role, $profile_photo) {
        $this->id_user = $id_user;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->first_surname = $first_surname;
        $this->second_surname = $second_surname;
        $this->birth_date = $birth_date;
        $this->gender = $gender;
        $this->id_role = $id_role;
        $this->profile_photo = $profile_photo;
    }

    /**
     * Get the value of id_user
     */ 
    public function getId_user()
    {
        return $this->id_user;
    }

    /**
     * Set the value of id_user
     *
     * @return  self
     */ 
    public function setId_user($id_user)
    {
        $this->id_user = $id_user;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of first_surname
     */ 
    public function getFirst_surname()
    {
        return $this->first_surname;
    }

    /**
     * Set the value of first_surname
     *
     * @return  self
     */ 
    public function setFirst_surname($first_surname)
    {
        $this->first_surname = $first_surname;

        return $this;
    }

    /**
     * Get the value of second_surname
     */ 
    public function getSecond_surname()
    {
        return $this->second_surname;
    }

    /**
     * Set the value of second_surname
     *
     * @return  self
     */ 
    public function setSecond_surname($second_surname)
    {
        $this->second_surname = $second_surname;

        return $this;
    }

    /**
     * Get the value of id_role
     */ 
    public function getId_role()
    {
        return $this->id_role;
    }

    /**
     * Set the value of id_role
     *
     * @return  self
     */ 
    public function setId_role($id_role)
    {
        $this->id_role = $id_role;

        return $this;
    }

    /**
     * Get the value of profile_photo
     */ 
    public function getProfile_photo()
    {
        return $this->profile_photo;
    }

    /**
     * Set the value of profile_photo
     *
     * @return  self
     */ 
    public function setProfile_photo($profile_photo)
    {
        $this->profile_photo = $profile_photo;

        return $this;
    }

    /**
     * Get the value of birth_date
     */ 
    public function getBirth_date()
    {
        return $this->birth_date;
    }

    /**
     * Set the value of birth_date
     *
     * @return  self
     */ 
    public function setBirth_date($birth_date)
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    /**
     * Get the value of gender
     */ 
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set the value of gender
     *
     * @return  self
     */ 
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }
}