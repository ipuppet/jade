<?php


namespace Ipuppet\Jade\Plugins\Office;


use PhpOffice\PhpSpreadsheet\Exception as PhpOfficeException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpOfficeReaderException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Psr\Log\LoggerInterface;

class Excel
{
    private static $instance;
    private $logger;

    /**
     * Excel constructor.
     * @param LoggerInterface|null $logger
     * @throws Exception
     */
    private function __construct(LoggerInterface $logger = null)
    {
        if (!class_exists('PhpOffice\PhpSpreadsheet\Exception') ||
            !class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ||
            !class_exists('PhpOffice\PhpSpreadsheet\Reader\Exception') ||
            !class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ||
            !class_exists('PhpOffice\PhpSpreadsheet\Writer\Exception')
        ) {
            throw new Exception('您并未安装扩展或者扩展包不完整，您可以尝试运行：composer require phpoffice/phpspreadsheet');
        }
        $this->logger = $logger;
    }

    public static function getInstance(LoggerInterface $logger = null): self
    {
        if (null == self::$instance)
            self::$instance = new self($logger);
        return self::$instance;
    }

    public function setOptions($options)
    {
        $keys = array_keys(get_class_vars(__CLASS__));
        foreach ($options as $key => $value) {
            if (in_array($key, $keys)) {
                $this->$key = $value;
            }
        }
    }

    public function read($file)
    {
        try {
            $spreadsheet = IOFactory::load($file);
        } catch (PhpOfficeReaderException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        try {
            $sheet = $spreadsheet->getActiveSheet();
        } catch (PhpOfficeException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        $res = array();
        //从第一行开始读取数据，第一行作为索引
        foreach ($sheet->getRowIterator(1) as $row) {
            $tmp = array();
            $i = 0;
            foreach ($row->getCellIterator() as $cell) {
                //用来生成关联数组
                if (count($res) >= 1) {
                    $tmp[$res[1][$i]] = $cell->getFormattedValue();
                } else {
                    $tmp[] = $cell->getFormattedValue();
                }
                $i++;
            }
            $res[$row->getRowIndex()] = $tmp;
        }
        unset($res[1]);
        return $res;
    }

    public function write($title, $data)
    {
        $excel = new Spreadsheet();  //创建一个新的excel文档
        try {
            $objSheet = $excel->getActiveSheet();
        } catch (PhpOfficeException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }  //获取当前操作sheet的对象
        $objSheet->setTitle($title);  //设置当前sheet的标题

        //设置列 设置宽度为true,不然太窄了

        //生成列索引
        $c = 65;
        $columns = array_keys($data[0]);
        foreach ($columns as $column) {
            $columnDimension[$column] = strtoupper(chr($c));
            $c++;
            try {
                $excel->getActiveSheet()->getColumnDimension($columnDimension[$column])->setAutoSize(true);
            } catch (PhpOfficeException $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }

        foreach ($columns as $column) {
            $objSheet->setCellValue($columnDimension[$column] . '1', $column);
        }
        //写入数据
        foreach ($data as $index => $row) {
            $index = $index + 2;
            foreach ($row as $column => $value) {
                $objSheet->setCellValue($columnDimension[$column] . $index, $value);
            }
        }
        return $excel;
    }

    /**
     * 下载文件
     * @param $excel
     * @param $filename
     * @param $type
     */
    public function download($excel, $filename, $type = 'Xlsx')
    {
        // $type只能为 Xlsx 或 Xls
        if ($type == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } elseif ($type == 'Xls') {
            header('Content-Type: application/vnd.ms-excel');
        }

        header("Content-Disposition: attachment;filename="
            . $filename . date('Y-m-d') . '.' . strtolower($type));
        header('Cache-Control: max-age=0');
        try {
            $objWriter = IOFactory::createWriter($excel, $type);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        try {
            $objWriter->save('php://output');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
