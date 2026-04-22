<?php
namespace w3des\AdminBundle\Service;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Model\ValueList;

class Values
{

    protected $uploadDir;

    public function __construct($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function handleValues(ValueList $list, callable $new, callable $update, callable $remove )
    {
        $models = $list->getModels();
        $newModels = [];
        foreach ($list->getValues() as $value) {
            $pos = 0;
            foreach ($value->getDefinition()->array ? $value->getValue() : [$value->getValue()] as $num => $val) {
                $index = $value->getDefinition()->name . ($value->getDefinition()->locale ? '_' . $value->getLocale() : '');
                /** @var \w3des\AdminBundle\Model\ValueInterface $tmp */
                $tmp = null;
                if (isset($models[$index][$num])) {
                    $tmp = $models[$index][$num];
                } else {
                    $tmp = $new();
                    $tmp->setLocale($value->getLocale());
                    $tmp->setType($value->getDefinition()->storeType);
                    $tmp->setName($value->getDefinition()->name);
                }
                $tmp->setPos($pos);
                $this->setModelValue($tmp, $value->getDefinition(), $val);
                if (!isset($models[$index][$num])) {
                    $update($tmp);
                }
                $pos++;
                $newModels[] = $tmp;
            }
            if (isset($models[$index])) {
                foreach ($models[$index] as $model) {
                    if ($model->getPos() >= $pos) {
                        $remove($model);
                    }
                }
            }

        }
        $list->loadModels($newModels);

    }

    protected function setModelValue(ValueInterface $model, ValueDefinition $def, $value)
    {
        switch ($def->storeType) {
            case 'file':
                if (\is_string($value)) {
                    $file = new File(\realpath($this->uploadDir) . $value);
                    if ($file->isReadable()) {
                        $model->setStringValue($value);
                        $model->setSize($file->getSize());
                        $model->setMime($file->getMimeType());
                        $info = \getimagesize($file->getPathname());
                        $model->setWidth($info[0]);
                        $model->setHeight($info[1]);
                    }
                    break;
                }
                if ($value['file'] instanceof UploadedFile || ! empty($value['remove'])) {
                    // remove
                    $curr = $model->getValue();
                    if ($curr) {
                        $path = \realpath($this->uploadDir) . $curr['path'];
                        do {
                            if (\file_exists($path)) {
                                if (\is_file($path)) {
                                    unlink($path);
                                } else {
                                    $it = new \FilesystemIterator($path);
                                    if (\iterator_count($it) == 0) {
                                        \rmdir($path);
                                    }
                                }
                            }
                            $path = \dirname($path);
                        } while (strlen($path) > strlen($this->uploadDir));
                    }

                    $model->setStringValue(null);
                }
                if ($value['file'] instanceof UploadedFile) {
                    $tmp = new Slugify();
                    $source = $value['file']->getClientOriginalName();
                    if ($value['file']->getClientOriginalExtension()) {
                        $source = substr($source, 0, strlen($source) - 1 - strlen($value['file']->getClientOriginalExtension()));
                    }
                    $dir = isset($def->options['dir']) ? $def->options['dir'] : 'settings';
                    $path = '/' . $dir. '/' . date('Y') . '/' . date('m') . '/' . date('d');
                    if (! \file_exists(\realpath($this->uploadDir) . $path)) {
                        \mkdir(\realpath($this->uploadDir) . $path, 0777, true);
                    }
                    $name = \uniqid('', true) . '_' . $tmp->slugify($source) . '.' . \strtolower($value['file']->getClientOriginalExtension());
                    $model->setStringValue($path . '/' . $name);
                    $model->setMime($value['file']->getMimeType());
                    $model->setSize($value['file']->getSize());

                    $value['file']->move(\realpath($this->uploadDir) . $path, $name);

                    $info = \getimagesize($this->uploadDir . $model->getStringValue());
                    $model->setWidth($info[0]);
                    $model->setHeight($info[1]);
                }

                break;
            case 'text':
                $model->setTextValue($value);
                break;
            case 'datetime':
                $model->setDateTimeValue($value);
                break;
            case 'bool':
                $model->setIntValue($value ? 1 : 0);
                break;
            case 'integer':
                $model->setIntValue($value);
                break;
            default:
                $model->setStringValue($value);
        }
        $model->setType($def->storeType);
    }

    /**
     * @param ValueInterface[] $collection
     * @param $name
     * @param $locale
     *
     * @return ValueInterface[]
     */
    public function collectValues(\Traversable $collection, $name, $locale)
    {
        $res = [];
        /** @var \w3des\AdminBundle\Model\ValueInterface $item */
        foreach ($collection as $item) {
            if ($item->getLocale() == $locale && $item->getName() == $name) {
                $res[$item->getPos()] = $item->getValue();
            }
        }
        \ksort($res);

        return $res;
    }

    public function readValue(\Traversable $collection, ValueDefinition $cfg, $locale = '')
    {
        $coll = $this->collectValues($collection, $cfg->name, $locale);

        if ( !$cfg->array) {
            if(count($coll)) {
                return $coll[0];
            }

            return null;
        }

        return $coll;
    }
}

