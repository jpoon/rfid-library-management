import java.io.*;
import java.net.*;

public class PHPHandler {
	
	public static void main( String[] args ){
		PHPHandler myPHP = new PHPHandler();
		
		if( args.length == 0 ){
			System.err.println("Error: Procedure has no arguments." );
		}
		else if( args[0].equals("requestRFIDTagId")){
			myPHP.requestRFIDTagId();
		}
		else if( args[0].equals("downloadRFID") ){
			myPHP.downloadRFID();
		}
		else if ( args[0].equals("bookList")){
			myPHP.sendBookList(args);
		}
		else if( args[0].equals("userAuth")){
			myPHP.userAuth(args);
		}
		else{
			System.err.println("No Command Executed." );
		}
		
	}
	
	public PHPHandler(){
		super();
	}
	
	public void userAuth(String[] args){
		byte[] user = new byte[0x06];
		user[0] = 0x01;
		user[1] = 0x06;
		user[2] = 0x50;
		
		if( args[1].equals("Admin") || args[1].equals("Librarian") ){
			user[3] = 0x01;
		}
		else{
			user[3] = 0x02;
		}
		
		int[] userNum = BufferHelper.getUserID(Integer.parseInt(args[2]));
		user[4] = (byte) (userNum[0] & 0xFF);
		user[5] = (byte) (userNum[1] & 0xFF);
		
		String toSend = new String( user, 0, 6 );
		sendData(toSend);
	}
	
	public void sendBookList(String[] args){
		int numBooks = Integer.parseInt(args[1]);
		MySQLDatabase mySQL = new MySQLDatabase();
		
		int[] packet = new int[0x27];
		packet[0] = 0x01; //SOF
		packet[1] = 0x27; //Packet Length
		packet[2] = 0x10; //Command
		
		int userIDnum = Integer.parseInt(args[2]);
		int[] userIDnumArray = BufferHelper.getUserID(userIDnum);
		
		packet[3] = mySQL.getUserPriv( args[2] ); //User Privleges
		packet[4] = userIDnumArray[0] & 0xFF; // User ID High
		packet[5] =  userIDnumArray[1] & 0xFF; // User ID Low
		
		for( int i = 0; i < numBooks; i++ ){
			packet[6] = (i + 1); //Book No.
			
			String title = mySQL.getBookTitle(Long.parseLong(args[i+3]));
			for( int j = 0; j < 12; j++ ){ //Title
				try{
					packet[j+7] = title.charAt(j) & 0xFF;
				} catch( Exception ex ){
					packet[j+7] = 0x20;
				}
			}
			

			int[] rfidNumArray = BufferHelper.longToBytes(Long.parseLong(args[i+3]));			
			for( int k = 0; k < 8; k++ ){ //RFIDNum
				packet[k+19] =  rfidNumArray[k] & 0xFF;
			}
			

			String location = mySQL.getShelf(Long.parseLong(args[i+3]));
			for( int l = 0; l < 12; l++){
				try{
					packet[l+27] = location.charAt(l) & 0xFF;
				}catch( Exception ex ){ packet[l+27] = 0x20; }
				
			}
			
			byte[] toSendArray = new byte[0x27];
			for( int y = 0; y < packet.length; y++ ){
				toSendArray[y] =(byte) packet[y];
			}
			System.out.println();
			
			String toSend = new String(toSendArray, 0, 0x27);
			
			sendData(toSend);
			
			try{ 
				Thread.sleep(500);
			}catch( Exception ex ){ ex.printStackTrace(); }
		} // end packet build
		
	}
	
	public void requestRFIDTagId(){
		byte[] requestCommand = { 0x01, 0x03, 0x20 };
		String request = new String( requestCommand, 0 , 3);
		sendData( request );
	}
	
	public void downloadRFID(){
		recieveData();
	}
	
	public void sendSecurityresponse(byte r){
		byte[] toSend = new byte[4];
		toSend[0] = 0x01;
		toSend[1] = 0x04;
		toSend[2] = 0x61;
		toSend[3] = r;
		
		String toSendString = new String(toSend);
		sendData( toSendString );
	}
	
	public void sendCheckInOutResponse(byte r){
		byte[] response = new byte[4];
		response[0] = 0x01;
		response[1] = 0x04;
		response[2] = 0x31;
		response[3] = r;
		
		String toSendString = new String(response);
		sendData( toSendString );
	}
	
	private synchronized void sendData( String info ){
		
		try{
		Socket mySocket = new Socket("127.0.0.1", 6000);
		PrintWriter out = new PrintWriter(mySocket.getOutputStream());
		out.println(info);
		out.flush();
		} catch( IOException ex ){ ex.printStackTrace(); }
		System.out.println("Send Complete.");
		
	}
	
	private synchronized void recieveData(){
		try{
			//set up new socket, and tell the server "I am PHP!"
			Socket mySocket = new Socket( "127.0.0.1", 6000 );
			PrintWriter out = new PrintWriter(mySocket.getOutputStream());
			BufferedReader in = new BufferedReader( new InputStreamReader(mySocket.getInputStream()) );
			out.println("GET_RFID");
			out.flush();
			
			//Get information back from the controller
			//Convert to a long
		    //Print it to the system out stream so that the PHP picks it up
			String data;
			int[] rfidNum = new int[8];
			while( (data = in.readLine() ) != null ){
				byte[] incoming = data.getBytes();
				for( int i = 0; i < 8; i++ ){
					rfidNum[i] = incoming[i+3] & 0xFF;
				}
				long rfidNumLong = BufferHelper.bytesToLong(rfidNum, rfidNum.length);
				
				System.out.print(rfidNumLong);
				break;
			}
			
			mySocket.close();
			
		} catch( Exception ex ){ ex.printStackTrace(); }
	}
	
}
