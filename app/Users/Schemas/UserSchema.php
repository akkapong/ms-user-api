<?php

namespace Users\Schemas;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Neomerx\JsonApi\Schema\SchemaProvider;

/**
 * @package Neomerx\Samples\JsonApi
 */
class UserSchema extends SchemaProvider
{
    protected $resourceType = 'users';

    public function getId($user)
    {
        /** @var User $user */
        return (string)$user->_id;
    }

    public function getAttributes($user)
    {
        /** @var User $user */
        return [
            'username'   => $user->username,
            // 'password'   => $user->password,
            'ref_type'   => $user->ref_type,
            'ref_id'     => $user->ref_id,
            'status'     => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}