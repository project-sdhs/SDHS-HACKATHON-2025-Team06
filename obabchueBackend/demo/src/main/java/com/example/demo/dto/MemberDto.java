package com.example.demo.dto;

import com.example.demo.entity.Member;
import io.swagger.v3.oas.annotations.media.Schema;

@Schema(description = "회원 정보 DTO")
public class MemberDto {
    @Schema(description = "사용자 ID", example = "user123")
    private String userId;
    @Schema(description = "이름", example = "홍길동")
    private String name;
    @Schema(description = "전화번호", example = "01012345678")
    private int phone;
    @Schema(description = "성별 (0: 남성, 1: 여성 등)", example = "0")
    private int gender;
    @Schema(description = "비밀번호", example = "password123")
    private String password;

    public MemberDto() {}

    public MemberDto(String userId, String name, int phone, int gender, String password) {
        this.userId = userId;
        this.name = name;
        this.phone = phone;
        this.gender = gender;
        this.password = password;
    }

    public String getUserId() { return userId; }
    public void setUserId(String userId) { this.userId = userId; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public int getPhone() { return phone; }
    public void setPhone(int phone) { this.phone = phone; }
    public int getGender() { return gender; }
    public void setGender(int gender) { this.gender = gender; }
    public String getPassword() { return password; }
    public void setPassword(String password) { this.password = password; }

    public Member toEntity(){
        return new Member(userId, name, phone, gender, password);
    }
}
