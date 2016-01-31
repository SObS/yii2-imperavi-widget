<?php

namespace vova07\imperavi\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\web\BadRequestHttpException;

/**
 * Class ImageDeleteAction
 * @package vova07\imperavi\actions
 *
 * Usage:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'images-delete' => [
 *             'class' => ImageDeleteAction::className(),
 *             'filename' => Yii::$app->getRequest()->get('filename'),
 *             'url' => 'http://my-site.com/uploads/thumbs/',
 *             'thumbPath' => '@frontend/web/uploads/thumbs/',
 *             'imagePath' => '@frontend/web/uploads/images/',
 *         ]
 *     ];
 * }
 * ```
 *
 * @link https://github.com/SmileMD
 */
class ImageDeleteAction extends Action
{
    /**
     * @var string File name
     */
    public $filename;

    /**
     * @var string Thumbs directory
     */
    public $thumbPath;

    /**
     * @var string Images directory
     */
    public $imagePath;

    /**
     * @var string directory URL
     */
    public $url;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->filename === null) {
            throw new InvalidConfigException('The "filename" attribute must be set.');
        }
        if ($this->url === null) {
            throw new InvalidConfigException('The "url" attribute must be set.');
        }
        if ($this->thumbPath === null) {
            throw new InvalidConfigException('The "thumbPath" attribute must be set.');
        } else {
            $this->thumbPath = Yii::getAlias($this->thumbPath);
        }
        if ($this->imagePath === null) {
            throw new InvalidConfigException('The "imagePath" attribute must be set.');
        } else {
            $this->imagePath = Yii::getAlias($this->imagePath);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->request->isAjax) {

            $thumb = $this->thumbPath . $this->filename;
            $image = $this->imagePath . $this->filename;

            if(file_exists($thumb)){
                unlink($thumb);
            }

            if(file_exists($image)){
                unlink($image);
            }

            $list = [
                'url' => $this->url . $this->filename,
            ];

            Yii::$app->response->format = Response::FORMAT_JSON;

            return $list;
        } else {
            throw new BadRequestHttpException('Only AJAX is allowed');
        }
    }
}
