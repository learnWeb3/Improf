<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\NotFoundError;
use Application\Helper\StringUtils;
use Application\Service\ZoomOAuth;

class ZoomCredential extends ApplicationRecord
{

    public static function construct(array $fields)
    {
        // attibutes initialization
        $zoom_credential = new self;
        foreach ($fields as $field_name => $field_value) {
            $zoom_credential->$field_name = $field_value;
        }
        // associations
        $zoom_credential->belongs_to('user');
        return $zoom_credential;
    }

    public static function getOne(Params $params): self
    {
        try {
            $zoom_credential = ZoomCredential::select(["*"])
                ->from('zoom_credentials')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if(isset($zoom_credential[0])){
            $zoom_credential = $zoom_credential[0];
            return $zoom_credential;
        }else{
            $model_name = StringUtils::toModelName(get_called_class());
            $values = StringUtils::toQueryString(get_object_vars($params));
            $message = "$model_name not found using values $values";
            throw new NotFoundError([
                "zoom_authorization_url" => ZoomOAuth::requestAuthorizeUrl(),
                $message
            ]);
        }
    }

    public static function register(Params $params): self
    {
        try {
            return ZoomCredential::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params): self
    {
        try {
            ZoomCredential::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $zoom_credential = ZoomCredential::select(["*"])
                ->from('zoom_credentials')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $zoom_credential;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }


    public static function remove(Params $params): void
    {
        try {
            ZoomCredential::destroy()
                ->from('zoom_credntials')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function refreshToken(Params $params): self
    {
        $zoom_credentials = ZoomOAuth::refreshToken($params->refresh_token);
        $access_token = $zoom_credentials['access_token'];
        $refresh_token = $zoom_credentials['refresh_token'];
        $params->access_token = $access_token;
        $params->refresh_token = $refresh_token;
        return self::amend($params);
    }
}
