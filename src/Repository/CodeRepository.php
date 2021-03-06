<?php

namespace CViniciusSDias\RecargaTvExpress\Repository;

use CViniciusSDias\RecargaTvExpress\Model\Code;
use CViniciusSDias\RecargaTvExpress\Model\Sale;
use PDO;

class CodeRepository
{
    private $con;

    public function __construct(PDO $con)
    {
        $this->con = $con;
    }

    public function attachCodeToSale(Code $serialCode, Sale $sale): bool
    {
        $costumerEmail = $sale->costumerEmail;
        $sql = 'UPDATE serial_codes SET user_email = ? WHERE id = ?;';
        $stm = $this->con->prepare($sql);
        $stm->bindValue(1, $costumerEmail);
        $stm->bindValue(2, $serialCode->id, PDO::PARAM_INT);

        return $stm->execute();
    }

    public function findNumberOfAvailableCodes()
    {
        $sql = 'SELECT product, COUNT(id) AS total FROM serial_codes WHERE user_email IS NULL GROUP BY product;';
        $stm = $this->con->query($sql);

        return $stm->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function findUnusedCodes(int $numberOfAnnualSales, int $numberOfMonthlySales)
    {
        $stmt = $this->con->prepare("
        SELECT * FROM (SELECT product, id, serial FROM serial_codes WHERE user_email IS NULL AND product = 'anual' LIMIT :annual) AS anual
        UNION
        SELECT * FROM (SELECT product, id, serial FROM serial_codes WHERE user_email IS NULL AND product = 'mensal' LIMIT :monthly) AS mensal;
        ");
        $stmt->bindValue(':annual', $numberOfAnnualSales, PDO::PARAM_INT);
        $stmt->bindValue(':monthly', $numberOfMonthlySales, PDO::PARAM_INT);
        $stmt->execute();

        $grouppedCodes = $stmt->fetchAll(\PDO::FETCH_GROUP);
        $grouppedSerialCodes = [
            'anual' => [],
            'mensal' => [],
        ];
        foreach ($grouppedCodes as $product => $codes) {
            $grouppedSerialCodes[$product] = array_map(function (array $code) {
                return new Code($code['id'], $code['serial']);
            }, $codes);
        }

        return $grouppedSerialCodes;
    }
}
