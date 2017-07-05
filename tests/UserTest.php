<?php

class UserTest extends TestCase
{
    public function testRegisterOk()
    {
        $this->post('/users/register', factory());

        $this->assertResponseOk();
    }
}