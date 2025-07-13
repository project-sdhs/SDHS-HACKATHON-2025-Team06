package com.example.demo.controller;

import com.example.demo.dto.MemberDto;
import com.example.demo.entity.Member;
import com.example.demo.repositroy.MemberRepository;
import org.apache.juli.logging.Log;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.http.ResponseEntity;
import java.util.HashMap;
import java.util.Map;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.responses.ApiResponse;
import io.swagger.v3.oas.annotations.responses.ApiResponses;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.SignatureAlgorithm;
import io.swagger.v3.oas.annotations.media.Content;
import io.swagger.v3.oas.annotations.media.Schema;
import org.springframework.web.bind.annotation.RequestParam;
import java.util.Date;

@RestController
public class MemberController {
    @Autowired
    private MemberRepository memberRepository;

    private static final String SECRET_KEY = "obabchuSecretKey123!@#";

    @Operation(summary = "회원가입", description = "회원 정보를 받아 회원가입을 처리합니다.\n- userId(아이디), name(이름), age(나이), gender(성별), password(비밀번호)를 입력받아 회원가입을 진행합니다.\n- 이미 존재하는 userId는 덮어쓸 수 있습니다.")
    @ApiResponses({
        @ApiResponse(responseCode = "200", description = "회원가입 성공 또는 실패 메시지 반환",
            content = @Content(schema = @Schema(implementation = String.class)))
    })
    @PostMapping("api/signUp")
    public String signUp(@RequestBody MemberDto member) {
        //1. DTO 를 엔티티로 변환
        Member m = member.toEntity();

        //2. 레포지터리로 엔티티를 db에 저장
        Member saved = memberRepository.save(m);
        return saved!=null?"회원가입에 성공했습니다":"회원가입에 성공했습니다.";
    }
    @Operation(summary = "로그인", description = "아이디와 비밀번호로 로그인합니다.\n- 성공 시 JWT 토큰과 유저 정보를 반환합니다.\n- 실패 시 실패 메시지를 반환합니다.\n\nRequest 예시: {\"userId\":\"user123\", \"password\":\"pw123\"}")
    @ApiResponses({
        @ApiResponse(responseCode = "200", description = "로그인 성공/실패 여부, 메시지, JWT 토큰, 유저 정보 반환",
            content = @Content(schema = @Schema(example = "{\n  'success': true,\n  'message': '로그인에 성공하셨습니다!',\n  'token': 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',\n  'userId': 'user123',\n  'name': '홍길동'\n}")))
    })
    @PostMapping("api/signIn")
    public ResponseEntity<?> signIn(@RequestBody MemberDto member) {
        Member m = member.toEntity();
        Member readMember = memberRepository.findById(m.getUserId()).orElse(null);
        Map<String, Object> result = new HashMap<>();
        if(readMember != null && readMember.getPassword().equals(m.getPassword())) {
            // JWT 토큰 생성
            String jwt = Jwts.builder()
                    .setSubject(readMember.getUserId())
                    .claim("name", readMember.getName())
                    .setIssuedAt(new Date())
                    .setExpiration(new Date(System.currentTimeMillis() + 1000 * 60 * 60 * 24)) // 24시간
                    .signWith(SignatureAlgorithm.HS256, SECRET_KEY.getBytes())
                    .compact();
            result.put("success", true);
            result.put("message", "로그인에 성공하셨습니다!");
            result.put("token", jwt);
            result.put("userId", readMember.getUserId());
            result.put("name", readMember.getName());
        } else {
            result.put("success", false);
            result.put("message", "로그인에 실패하셨습니다");
        }
        return ResponseEntity.ok(result);
    }
}
