<?php

namespace vova07\imperavi\actions;

use vova07\imperavi\Widget;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\imagine\Image;
use Imagine\Image\Box;
use Yii;

/**
 * Class ImageUploadAction
 * @package vova07\imperavi\actions
 *
 * Usage:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'upload-image' => [
 *             'class' => ImageUploadAction::className(),
 *             'url' => 'http://my-site.com/uploads/thumbs/',
 *             'thumbPath' => '@frontend/web/uploads/thumbs/',
 *             'imagePath' => '@frontend/web/uploads/images/',
 *             'options' => [
 *                 'thumbWidth' => '260', //max thumb size
 *                 'thumbHeight' => '260', //max thumb size
 *                 'imageWidth' => '1170', //max image size
 *                 'imageHeight' => '1170', //max image size
 *               ],
 *             'validatorOptions' => [
 *                 'maxWidth' => 1000,
 *                 'maxHeight' => 1000,
 *                 'minWidth' => 100,
 *                 'minHeight' => 100,
 *             ]
 *         ]
 *     ];
 * }
 * ```
 *
 * @link https://github.com/SmileMD
 */
class ImageUploadAction extends Action
{
    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    public $thumbPath;

    /**
     * @var
     */
    public $imagePath;

    /**
     * @var array Image options
     */
    public $options = [];

    /**
     * @var boolean If `true` unique filename will be generated automatically
     */
    public $unique = true;

    /**
     * @var string Variable's name that Imperavi Redactor sent upon image/file upload.
     */
    public $uploadParam = 'file';

    /**
     * @var array Model validator options
     */
    public $validatorOptions = [];

    /**
     * @var string Model validator name
     */
    private $_validator = 'image';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->url === null) {
            throw new InvalidConfigException('The "url" attribute must be set.');
        } else {
            $this->url = rtrim($this->url, '/') . '/';
        }

        if ($this->thumbPath === null) {
            throw new InvalidConfigException('The "thumbPath" attribute must be set.');
        } else {
            $this->thumbPath = rtrim(Yii::getAlias($this->thumbPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (!FileHelper::createDirectory($this->thumbPath)) {
                throw new InvalidCallException("Directory specified in 'path' attribute doesn't exist or cannot be created.");
            }
        }

        if ($this->imagePath === null) {
            throw new InvalidConfigException('The "imagePath" attribute must be set.');
        } else {
            $this->imagePath = rtrim(Yii::getAlias($this->imagePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (!FileHelper::createDirectory($this->imagePath)) {
                throw new InvalidCallException("Directory specified in 'imagePath' attribute doesn't exist or cannot be created.");
            }
        }

        Widget::registerTranslations();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName($this->uploadParam);
            $model = new DynamicModel(compact('file'));
            $model->addRule('file', $this->_validator, $this->validatorOptions)->validate();

            if ($model->hasErrors()) {
                $result = [
                    'error' => $model->getFirstError('file')
                ];
            } else {
                if ($this->unique === true && $model->file->extension) {
                    $model->file->name = uniqid() . '.' . $model->file->extension;
                }

                if ($model->file->saveAs($this->imagePath . $model->file->name)) {

                    //crop image
                    Image::thumbnail($this->imagePath . $model->file->name, $this->options['thumbWidth'], $this->options['thumbHeight'])
                        ->save($this->thumbPath . $model->file->name, ['quality' => 90]);

                    //resize image maintaining aspect ratio
                    Image::frame($this->imagePath . $model->file->name, 0, '666', 0)
                        ->thumbnail(new Box($this->options['imageWidth'], $this->options['imageHeight']))
                        ->save($this->imagePath . $model->file->name, ['quality' => 90]);

                    $result = [
                        'filelink' => $this->url . $model->file->name
                    ];
                } else {
                    $result = [
                        'error' => Yii::t('vova07/imperavi', 'ERROR_CAN_NOT_UPLOAD_FILE')
                    ];
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $result;
        } else {
            throw new BadRequestHttpException('Only POST is allowed');
        }
    }
}
