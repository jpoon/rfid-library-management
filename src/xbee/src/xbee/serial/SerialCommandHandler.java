import java.net.*;
import java.io.*;

public class SerialCommandHandler {
	
	int[] command;
	int len;
	int commandType;
	MySQLDatabase mySQL;
	byte [] packetToSend;

	//constructor
	public SerialCommandHandler(int[] command, int len){
		this.command = command;
		this.len = len;
		if( !performChecks() ){
			System.out.println("Packet not of recognised format. Ignored.");
		}
		else{
			go();
		}
	}
	
	private boolean performChecks(){
		
		if( command[0] != 0x01 ){
			return false;
		}
		else if( len < 3 ){
			return false;
		}
		else if( command[1] != len ){
			return false;
		}
		else if( command[2] != 0x20 && command[2] != 0x21 && command[2] != 0x30 
				&& command[2] != 0x40 && command[2] != 0x41 && command[2] != 0x60){
			return false;
		}
		commandType = command[2];
		return true;
		
	}
	
	private void go(){
		
		switch (commandType){
			case 0x21 : downloadRFIDnum(); break;
			case 0x30 : modifyBookState(); break;
			case 0x40 : flagBookMisplaced(); break;
			case 0x41 : checkMisplacedList(); break;
			case 0x60 : securityCheck(); break;
			default : System.err.println("Command Type Not Found");
		}
	}
	
	private void downloadRFIDnum(){
		try{
			Socket mySocket = new Socket("127.0.0.1", 6000);
			PrintWriter out = new PrintWriter(mySocket.getOutputStream());
			packetToSend = new byte[command.length];
			for( int i = 0; i < command.length; i++  ){
				packetToSend[i] = (byte) (command[i] & 0xFF);
			}
			String output = new String(packetToSend, 0, packetToSend.length);
			out.println(output);
			out.flush();
		} catch( Exception ex ){ ex.printStackTrace(); }
	}
	
	private void modifyBookState(){
		mySQL = new MySQLDatabase();
		try{
			mySQL.modifyBookState(command);
		} catch( Exception ex ){ ex.printStackTrace();}
		mySQL.closeConnection();
	}
	
	private void flagBookMisplaced(){
		mySQL = new MySQLDatabase();
		try{
			mySQL.addToMisplacedTable(command);
		} catch( Exception ex ){ ex.printStackTrace(); }
		mySQL.closeConnection();
	}
	
	private void checkMisplacedList(){
		mySQL = new MySQLDatabase();
		try{
			mySQL.checkMisplacedShelves();
			mySQL.clearMisplacedCheckList();
		}catch( Exception ex ){ ex.printStackTrace(); }
		mySQL.closeConnection();
	}
	
	private void securityCheck(){
		mySQL = new MySQLDatabase();
		byte state = 0x02;
		try{
			state = mySQL.securityCheck(command);
		} catch( Exception ex ){ ex.printStackTrace(); }
		
		PHPHandler response = new PHPHandler();
		response.sendSecurityresponse(state);
		
	}
	
	
	
	
	
	
	
}
