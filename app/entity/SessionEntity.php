<?php

    class Session extends Crud {

        # The table you want to perform the database actions on
        protected $table = 'sessions';

        # Primary Key of the table
        protected $pk  = 'token';

    }