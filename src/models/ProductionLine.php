<?php


class ProductionLine
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function updateProductionLineBySmartBox(array $data): int
    {
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $digitexSmartBox = $data["digitex_smart_box"];

        $sql = "UPDATE chaine_201
                SET Firstname = :firstName, Lastname = :lastName
                WHERE DigiTex = :digitexSmartBox";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':firstName', $firstName);
        $stmt->bindValue(':lastName', $lastName);
        $stmt->bindValue(':digitexSmartBox', $digitexSmartBox);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }
}
