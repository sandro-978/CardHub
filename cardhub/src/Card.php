<?php
class Card
{
    public int $id;
    public string $name;
    public string $game;
    public string $edition;
    public string $language;

    public function __construct(int $id, string $name, string $game, string $edition, string $language)
    {
        $this->id = $id;
        $this->name = $name;
        $this->game = $game;
        $this->edition = $edition;
        $this->language = $language;
    }
}
?>
