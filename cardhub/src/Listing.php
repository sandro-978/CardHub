<?php
class Listing
{
    public int $id;
    public int $userId;
    public int $cardId;
    public float $price;
    public string $condition;
    public string $status;

    public function __construct(int $id, int $userId, int $cardId, float $price, string $condition, string $status = 'active')
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->cardId = $cardId;
        $this->price = $price;
        $this->condition = $condition;
        $this->status = $status;
    }
}
?>
