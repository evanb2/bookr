<?php


namespace App\Http\Response;


use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class FractalResponse
{
    private $manager;

    private $serializer;

    /**
     * FractalResponse constructor.
     * @param Manager $manager
     * @param SerializerAbstract $serializer
     */
    public function __construct(Manager $manager, SerializerAbstract $serializer)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->manager->setSerializer($serializer);
    }

    /**
     * @param $data
     * @param TransformerAbstract $transformer
     * @param null $resourceKey
     * @return array
     */
    public function item($data, TransformerAbstract $transformer, $resourceKey = NULL)
    {
        return $this->createDataArray(
            new Item($data, $transformer, $resourceKey)
        );
    }

    /**
     * @param $data
     * @param TransformerAbstract $transformer
     * @param null $resourceKey
     * @return array
     */
    public function collection($data, TransformerAbstract $transformer, $resourceKey = NULL)
    {
        return $this->createDataArray(
            new Collection($data, $transformer, $resourceKey)
        );
    }

    /**
     * @param ResourceInterface $resource
     * @return array
     */
    private function createDataArray(ResourceInterface $resource)
    {
        return $this->manager->createData($resource)->toArray();
    }
}