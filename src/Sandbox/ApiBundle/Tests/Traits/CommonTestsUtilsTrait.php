<?php

namespace Sandbox\ApiBundle\Tests\Traits;

trait CommonTestsUtilsTrait
{
    private function assertResponseIsAnEmptyArray()
    {
        $this->assertEmpty(
            $this->responseJson,
            'The response should be an empty array.'
        );
    }

    private function assertResponseHasSpecificItemsAmountArray($expectedAmount)
    {
        $this->assertCount(
            $expectedAmount,
            $this->responseJson,
            'The response does not contains the expected amount of item(s).'
        );
    }

    private function assertResponseFirstItemContainsCorrectDataFields($data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            $this->assertEquals(
                $data[$key],
                $this->responseJson[0][$key],
                'The expected '.$key.' is not correct.'
            );
        }
    }

    private function assertResponseContainsCorrectDataFields($data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            $this->assertEquals(
                $data[$key],
                $this->responseJson[$key],
                'The expected '.$key.' is not correct.'
            );
        }
    }

    private function assertResponseFirstItemContainsCorrectFieldsAmount($expectedAmount)
    {
        $this->assertCount(
            $expectedAmount,
            $this->responseJson[0],
            'The first item of the requests list does not contains the expected amount of fields.'
        );
    }

    private function assertResponseContainsCorrectFieldsAmount($expectedAmount)
    {
        $this->assertCount(
            $expectedAmount,
            $this->responseJson,
            'The first item of the requests list does not contains the expected amount of fields.'
        );
    }

    private function getCurrentAmountInDatabase(
        $repoString
    ) {
        $items = $this->em->getRepository('SandboxApiBundle:'.$repoString)->findAll();
        return count($items);
    }
}
