<?php
$tradeArray = array();
$correctTradeArray = array();
$row = 1;
if (($handle = fopen("./DefiChainOutput.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    $row++;
            $tradeArray[] =array("Type"=>$data[0],
        "Buy Amount"=>$data[1],
        "Buy Cur"=>$data[2],
        "Sell Amount"=>$data[3],
        "Sell Cur"=>$data[4],
        "Fee amount"=>$data[5],
        "Fee Cur"=>$data[6],
        "Exchange"=>$data[7],
        "Trade Group"=>$data[8],
        "Comment"=>$data[9],
        "Date"=>$data[10],
        "TX-ID"=>$data[11]);
        //echo $data[$c] . "<br />\n";
    
    // for ($c=0; $c < $num; $c++) {
    //     //1 Type
    //     //2 Buy Amount
    //     //3 Buy Cur
    //     //4 Sell Amount
    //     //5 Sell Cur
    //     //6 Fee amount
    //     //7 Fee Cur
    //     //8 Exchange
    //     //9 Trade Group
    //     //10 Comment
    //     //11 Date
    //     //12 TX-ID
    //     $tradeArray[] =array("Type"=>$data[0],
    //     "Buy Amount"=>$data[1],
    //     "Buy Cur"=>$data[2],
    //     "Sell Amount"=>$data[3],
    //     "Sell Cur"=>$data[4],
    //     "Fee amount"=>$data[5],
    //     "Fee Cur"=>$data[6],
    //     "Exchange"=>$data[7],
    //     "Trade Group"=>$data[8],
    //     "Comment"=>$data[9],
    //     "Date"=>$data[10],
    //     "TX-ID"=>$data[11]);
    //     //echo $data[$c] . "<br />\n";
    // }
  }
  fclose($handle);
}
$pointer =0;
$arrayHeaders = array_shift($tradeArray);
$correctTradeArray[] = $arrayHeaders;
foreach($tradeArray as $trade){
    //Find Withdrawals and Deposits
    $nextTrade = $tradeArray[$pointer+1];
    if(strripos($trade['Buy Cur'],"-")){
        //Check that next deposit is part of this deposit
        if($nextTrade['Buy Cur'] == $trade['Buy Cur'] && $nextTrade['TX-ID'] == $trade['TX-ID'] ){
            
        //   print_r($trade);
        //   echo "<br /><br />";
        //   print_r($nextTrade);
        //   echo "<br /><br />";
          $depositCurrencies = explode("-", $trade['Buy Cur']);

          //Split off first withdrawal
          $withdrawalOne = $trade;
          $withdrawalOne['Type'] = "Other Expense";
          $withdrawalOne['Buy Cur'] = "";
          $withdrawalOne['Buy Amount'] = "";
          $withdrawalOne['Sell Amount'] = $trade['Sell Amount'] ;
          $withdrawalOne['Comment'] = "Add liquidity ".$trade['Buy Cur'];
          $withdrawalOne['Trade Group'] = "";
          $withdrawalOne['Sell Cur'] = $depositCurrencies[0];     
        //   print_r($withdrawalOne);
        //   echo "<br /><br />";
         
          //Split off second withdrawal
          $withdrawalTwo = $nextTrade;
          $withdrawalTwo['Type'] = "Other Expense";
          $withdrawalTwo['Buy Cur'] = "";
          $withdrawalTwo['Buy Amount'] = "";
          $withdrawalTwo['Sell Amount'] = $nextTrade['Sell Amount'] ;
          $withdrawalTwo['Comment'] = "Add liquidity ".$nextTrade['Buy Cur'];
          $withdrawalTwo['Trade Group'] = "";
          $withdrawalTwo['Sell Cur'] = $depositCurrencies[1];     
        //   print_r($withdrawalTwo);

          //change $trade to correct deposit type
          $trade['Type'] = "Income (non taxable)";
          $trade['Sell Amount'] = "" ;
          $trade['Comment'] = "Added liquidity";
          $trade['Trade Group'] = "";
          $trade['Sell Cur'] = "";
     
          //Merge 2 transactions amount into single deposit
          $trade['Buy Amount'] = $trade['Buy Amount'] * 2;
        //   echo "<br /><br />";

                    
          //Remove "Second" trade cause it is a single deposit
          unset($nextTrade);
        //   print_r($trade);
        //   die();
        $correctTradeArray[] = $trade;  
        $correctTradeArray[] = $withdrawalOne;  
        $correctTradeArray[] = $withdrawalTwo;
      }
    }

      //Find Rewards
      //"Mining","0.00000042000000","BTC","","","","","DeFiChain Light Wallet","","Commission","2022-01-19T00:01:00", "BTC20220119000100_Commission"
      if($trade['Type'] == "Mining"){
          $trade['Type'] = "Reward / Bonus";
          $trade['Trade Group'] = "";
          $trade['Comment'] = "Liquidity mining reward ETH-DFI";
          $correctTradeArray[] = $trade;  
      }
$pointer ++;
}
$fp = fopen('correctTrade.csv', 'w');
  
// Loop through file pointer and a line
foreach ($correctTradeArray as $trade) {
    fputcsv($fp, $trade);
}
  
fclose($fp);
        print("<pre>".print_r($correctTradeArray,true)."</pre>");