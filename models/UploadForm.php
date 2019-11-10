<?php
/**
 * Created by PhpStorm.
 * User: KINHNB
 * Date: 11/10/2019
 * Time: 10:20 PM
 */

namespace app\models;


use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public $path;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $this->path = 'uploads/' . $this->file->baseName . '.' . $this->file->extension;
            $this->file->saveAs($this->path);
            return true;
        } else {
            return false;
        }
    }
}
