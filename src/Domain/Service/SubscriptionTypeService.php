<?php
namespace App\Domain\Service;

use App\Domain\Repository\SubscriptionTypeRepositoryInterface;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeUseCase;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeRequest;
use App\Application\UseCase\Owner\AddSubscriptionType\AddSubscriptionTypeResponse;

class SubscriptionTypeService
{
    private AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase;
    public function __construct(SubscriptionTypeRepositoryInterface $subscriptionTypeRepository)
    {
        $this->addSubscriptionTypeUseCase = new AddSubscriptionTypeUseCase($subscriptionTypeRepository);
    }

    public function addSubscriptionType(AddSubscriptionTypeRequest $request): AddSubscriptionTypeResponse
    {
        return $this->addSubscriptionTypeUseCase->execute($request);
    }
}
