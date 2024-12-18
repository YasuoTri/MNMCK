<?php
class Database
{
    private const HOST = 'localhost';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const DBNAME = 'bht_bookstore';

    private static function Connect()
    {
        try {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=utf8';
            $connect = new PDO($dsn, self::USERNAME, self::PASSWORD);
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $connect;
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Sử dụng cho câu truy vấn SELECT
     * @param string $query Câu truy vấn
     * @param array $format Định dạng kết quả trả về.
     * $format = ['row' => int, 'cell' => int|string]
     * @return array $arr
     */
    public static function GetData($query, $format = [])
    {
        $connect = self::Connect();
        $stmt = $connect->query($query);
        
        if ($stmt) {
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Trả về một giá trị theo key hoặc index
            if (isset($format['cell'])) {
                $formatRow = isset($format['row']) ? $format['row'] : 0;
                $formatKey = is_numeric($format['cell']) ? array_keys($arr[$formatRow])[$format['cell']] : $format['cell'];
                return isset($formatRow) ? $arr[$formatRow][$formatKey] : $arr[0][$formatKey];
            }

            // Trả về một dòng dữ liệu tại index
            if (isset($format['row'])) {
                return $arr[$format['row']];
            }

            return $arr;
        }
        
        return [];
    }

    /**
     * Sử dụng cho câu truy vấn SELECT có tính năng phân trang
     */
    public static function GetDataWithPagination($query, $offset = 10, $page = 1)
    {
        $countAll = self::GetData('SELECT count(*) FROM categories', ['cell' => 0]);

        $start = ($page - 1) * $offset;
        $data = self::GetData($query . " LIMIT $start, $offset");
        $end = $start + count($data);
        return [
            'data'        => $data,
            'start'       => $start + 1,
            'end'         => $end,
            'countAll'    => $countAll,
            'page_number' => ceil($countAll / $offset),
        ];
    }

    /**
     * Dùng cho truy vấn INSERT, UPDATE, DELETE
     */
    public static function NonQuery($query)
    {
        $connect = self::Connect();
        $stmt = $connect->prepare($query);
        
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}